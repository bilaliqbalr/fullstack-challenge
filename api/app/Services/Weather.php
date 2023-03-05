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
        $data = self::getCache($this->user->latitude, $this->user->longitude);
        if (!is_array($data)) {
            return [];
        } else {
            return $data;
        }
    }

    /**
     * @throws WeatherHttpException
     */
    public function save() {
        $hasCache = self::getCache($this->user->latitude, $this->user->longitude);

        $forecast = $this->getForecastByCoords($this->user->latitude, $this->user->longitude);

        if ((!$hasCache || $hasCache == []) && !empty($forecast)) {
            // Only create history if won't have cache, means it will be new data from API
            $this->user->forecastHistory()->create([
                'forecast' => $forecast
            ]);
        }
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
        $data = self::getCache($lat, $long);
        if ($data !== false) {
            return $data;
        }

        try {
            $response = Http::acceptJson()->get(self::getPointEndpoint($lat, $long));
        } catch (\Exception $e) {
            throw new WeatherHttpException($e->getMessage());
        }

        if ($response->successful()) {
            $forecastUrl = $response->json('properties.forecast');
            // Avoid forecast having null URL
            if (is_null($forecastUrl)) {
                $forecast = [];
            } else {
                $forecast = self::getForecast($forecastUrl);
            }

            // Caching forecast data for later use to avoid multiple API calls
            Cache::put(self::getCacheKey($lat, $long), $forecast, 60 * 60);

            return $forecast;

        } else {
            if ($response->json('title') == "Unexpected Problem") {
                // Avoid making API calls in case of invalid coordinates
                // Will receive this message whenever it is invalid coordinates
                Cache::put(self::getCacheKey($lat, $long), [], 60 * 60);
                return [];

            } else {
                throw new WeatherHttpException("Error getting response from point endpoint");
            }
        }
    }

    /**
     * @param $forecastUrl
     * @return array
     * @throws WeatherHttpException
     */
    public static function getForecast($forecastUrl) : array {
        try {
            $forecastResp = Http::acceptJson()->get($forecastUrl);
        } catch (\Exception $e) {
            throw new WeatherHttpException($e->getMessage());
        }

        if ($forecastResp->successful()) {
            return $forecastResp->json('properties.periods');

        } else {
            throw new WeatherHttpException("Error getting response from forecast endpoint");
        }
    }
}
