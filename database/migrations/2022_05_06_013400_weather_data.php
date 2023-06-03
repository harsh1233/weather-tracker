<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WeatherData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->integer('dt')->unique();
            $table->string('dt_txt', 19)->unique();
            $table->string('cape_town_main', 255)->nullable();
            $table->string('cape_town_description', 255)->nullable();
            $table->string('johannesburg_main', 255)->nullable();
            $table->string('johannesburg_description', 255)->nullable();
            $table->string('delhi_main', 255)->nullable();
            $table->string('delhi_description', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::unprepared('CREATE TRIGGER weather_data_updated_at AFTER UPDATE ON weather_data
            FOR EACH ROW
            BEGIN
                UPDATE weather_data SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
            END;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
