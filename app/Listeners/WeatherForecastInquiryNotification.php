<?php

namespace App\Listeners;

use App\Events\WeatherForecastInquiryEvent;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeatherForecastInquiryNotification
{
    private const openweathermap_url = 'https://api.openweathermap.org/data/2.5/forecast';
    private const openweathermap_appid = '4c7f1f68689243332f5672f3f5d973e0';
    private const city_data = [
        ['-33.9249', '18.4241', 'cape_town'],
        ['-26.2041', '28.0473', 'johannesburg'],
        ['28.6139', '77.2090', 'delhi']
    ];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\WeatherForecastInquiryEvent  $event
     * @return void
     */
    public function handle(WeatherForecastInquiryEvent $event)
    {
        $weather_dt_cities_list = $this->getWeatherFromOpenWeatherMap();

        $columns_to_be_updated = [
            'cape_town_main', 'cape_town_description', 'johannesburg_main', 'johannesburg_description',
            'delhi_main', 'delhi_description'
        ];

        DB::table('weather_data')->upsert($weather_dt_cities_list, ['dt'], $columns_to_be_updated);
    }


    private function getWeatherFromOpenWeatherMap()
    {
        $openweathermap_responses = Http::pool(fn (Pool $pool) => [
            $pool->get(self::openweathermap_url, ['lat' => self::city_data[0][0], 'lon' => self::city_data[0][1], 'appid' => self::openweathermap_appid]),
            $pool->get(self::openweathermap_url, ['lat' => self::city_data[1][0], 'lon' => self::city_data[1][1], 'appid' => self::openweathermap_appid]),
            $pool->get(self::openweathermap_url, ['lat' => self::city_data[2][0], 'lon' => self::city_data[2][1], 'appid' => self::openweathermap_appid]),
        ]);

        $dt_array = [];
        $weather_dt_list = [];
        $num_city_data = count($openweathermap_responses);
        for ($i = 0; $i < $num_city_data; $i++) {
            $openweathermap_json = $openweathermap_responses[$i]->json();
            $weather_dt_list[$i] = $this->getWeatherDtList($openweathermap_json, self::city_data[$i][2]);
            $dt_array = array_merge($dt_array, array_keys($weather_dt_list[$i]));
        }

        $dt_array = array_unique($dt_array);
        $weather_dt_cities_list = [];
        foreach ($dt_array as $dt) {
            $weather_dt = [];
            for ($i = 0; $i < $num_city_data; $i++) {
                $weather_contensts_map_with_dt = $weather_dt_list[$i][$dt];
                if (isset($weather_contensts_map_with_dt)) {
                    $weather_dt += $weather_contensts_map_with_dt;
                }
            }
            $weather_dt_cities_list[] = $weather_dt;
        }

        return $weather_dt_cities_list;
    }


    private function getWeatherDtList($openweathermap_json, $city)
    {
        $weather_list = $openweathermap_json['list'];
        $weather_dt_list = [];

        foreach ($weather_list as $weather_item) {
            $dt = $weather_item['dt'];
            $weather_dt_item['dt'] = $weather_item['dt'];
            $weather_dt_item['dt_txt'] = $weather_item['dt_txt'];
            $weather_contents = $weather_item['weather'];
            if (!empty($weather_contents) && !empty($weather_contents[0])) {
                if (isset($weather_contents[0]['main'])) {
                    $weather_dt_item[$city . '_main'] = $weather_contents[0]['main'];
                }
                if (isset($weather_contents[0]['description'])) {
                    $weather_dt_item[$city . '_description'] = $weather_contents[0]['description'];
                }
            }
            $weather_dt_list[$dt] = $weather_dt_item;
        }

        return $weather_dt_list;
    }
}
