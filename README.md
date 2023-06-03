# setup

1. Prepare vendor directory with the following command

```
$ composer install
```

2. Prepare .env file

- You can use .env.weather-forecast as a reference file.

3. Prepare the database tables with the following command.

```
$ php artisan migrate
```

4. Prepare a crontab setting (It's not required for a simple Web API test.)

- This code collects weather forecasts for Cape Town, Johannesburg, Delhi every 10 minutes.
- You need to add a single cron configuration entry to your server that runs the schedule:run command every minute.

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

# a simple Web API test

1. Start server for a simple test

```
$ php artisan serve
```

2. Access the following address

```
http://localhost:8000/api/get-weather-forecast?date=2022-05-13 10:25:49
```

- The format of the date parameter is 'YYYY-MM-DD HH:mm:ss'.
- An example is '2022-05-13 10:25:49'.
- If you specify a date and time within 5 days of today, you will get the weather information.
- The following is an example of JSON response given by this Web API.

```
{
    "id": "9",
    "dt": "1652432400",
    "dt_txt": "2022-05-13 09:00:00",
    "cape_town_main": "Clouds",
    "cape_town_description": "overcast clouds",
    "johannesburg_main": "Clouds",
    "johannesburg_description": "overcast clouds",
    "delhi_main": "Clouds",
    "delhi_description": "scattered clouds",
    "updated_at": "2022-05-12 06:11:20",
    "created_at": "2022-05-12 06:11:20"
}
```

- First, it tries to retrieve data from the local server.
- If it fails to retrieve data from the server, it accesses the following site to collect weather information.
- If the weather information cannot be retrieved from the following site, an error response is returned.