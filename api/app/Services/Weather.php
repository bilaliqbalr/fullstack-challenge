<?php

namespace App\Services;


use App\Exceptions\WeatherHttpException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Weather
{
    private static string $endpoint = "https://api.weather.gov/";

    public function __construct (protected User $user) {
    }

    public static function getPointEndpoint($lat, $long) : string {
        return self::$endpoint . "points/{$lat},{$long}";
    }

    public function get() : ?array {
        try {
            return $this->getForecastByCoords($this->user->latitude, $this->user->longitude);

        } catch (WeatherHttpException $e) {
            return null;
        }
    }

    /**
     * @throws WeatherHttpException
     */
    public function save() {
        $forecast = $this->getForecastByCoords($this->user->latitude, $this->user->longitude);

        $this->user->forecastHistory()->update([
            'forecast' => $forecast
        ]);
    }

    public static function getCacheKey($lat, $long) : string {
        return "forecast-coords-$lat-$long";
    }

    /**
     * @param $lat
     * @param $long
     * @return false|mixed
     */
    public static function getCache($lat, $long) : mixed {
        $key = self::getCacheKey($lat, $long);

        if (Cache::has($key)) {
            return Cache::get($key);
        } else {
            return false;
        }
    }

    /**
     * @param $lat
     * @param $long
     * @return array
     * @throws WeatherHttpException
     */
    public static function getForecastByCoords($lat, $long) : array {
        // Will return cached data, in case another user have same latitude, longitude
        // to avoid multiple API requests
        if ($data = self::getCache($lat, $long)) {
            return $data;
        }

        $response = Http::acceptJson()->get(self::getPointEndpoint($lat, $long));

        if ($response->successful()) {
            $forecastUrl = $response->json('properties.forecast');
            $forecast = self::getForecast($forecastUrl);

            // Caching forecast data for later use to avoid multiple API calls
            Cache::put(
                self::getCacheKey($lat, $long),
                $forecast,
                60 * 60
            );

            return $forecast;

        } else {
            throw new WeatherHttpException("Error getting response from point endpoint");
        }
    }

    /**
     * @param $forecastUrl
     * @return array
     * @throws WeatherHttpException
     */
    public static function getForecast($forecastUrl) : array {
        $forecastResp = Http::acceptJson()->get($forecastUrl);

        if ($forecastResp->successful()) {
            return $forecastResp->json('properties.forecast');

        } else {
            throw new WeatherHttpException("Error getting response from forecast endpoint");
        }
    }
}
