<?php

namespace App\Http\Controllers;

use App\Events\WeatherForecastInquiryEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;

class WeatherForecastInquiryController extends Controller
{
    public function getWeatherForecast(Request $request)
    {

        $dt_txt = $request->date;
        if (!preg_match('/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\z/', $dt_txt)) {
            $return_value = ['Result' => 'Failed', 'Error' => 'Format Error', 'Date' => $request->date];
            return response()->json($return_value);
        }

        $dt = 0;

        try {
            $dt_obj = new DateTime($dt_txt);
            $dt = $dt_obj->getTimestamp();
        } catch (\Exception $ex) {
            $dt = false;
        }

        if ($dt === false || $dt === 0) {
            $return_value = ['Result' => 'Failed', 'Error' => 'Incorrect Date', 'Date' => $request->date];
            return response()->json($return_value);
        }

        $dt_one_and_a_half_hours_ago = $dt - 5400;
        $dt_one_and_a_half_hours_later = $dt + 5400;

        $cache_key = $dt_txt;

        // Check if the data is already cached.
        if (Cache::has($cache_key)) {
            $weather_data = Cache::get($cache_key);
        } else {
            // Fetch the data from the database.
            $weather_data = DB::table('weather_data')
                ->where('dt', '>=', $dt_one_and_a_half_hours_ago)
                ->where('dt', '<', $dt_one_and_a_half_hours_later)->get();

            // Cache the data.
            Cache::put($cache_key, $weather_data, 60 * 60 * 24); // Cache the data for 24 hours.
        }

        // Return the weather data.
        $weather_response_result = ['Result' => 'Success'];
        $weather_response = $weather_data->toArray()[0];
        $weather_response = json_decode(json_encode($weather_response), true);
        $weather_response = array_merge($weather_response_result, $weather_response);
        return response()->json($weather_response);
    }
}
