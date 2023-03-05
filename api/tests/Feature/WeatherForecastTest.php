<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Weather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherForecastTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test weather forecast command.
     *
     * @return void
     */
    public function test_weather_forecast_and_stores_in_cache_and_db()
    {
        // Creating test users
        $user = User::factory()->create([
            'latitude' => '38.8951',
            'longitude' => '-77.0364',
        ]);
        $user2 = User::factory()->create([
            'latitude' => '26.9391',
            'longitude' => '-99.2646',
        ]);
        $user3 = User::factory()->create([
            'latitude' => '25.7473',
            'longitude' => '-68.6278',
        ]);

        // Mock the HTTP client response for the API endpoint
        Http::fake([
            Weather::getPointEndpoint($user->latitude, $user->longitude) => Http::response([
                'properties' => [
                    'forecast' => 'https://api.weather.gov/gridpoints/LWX/96,78/forecast',
                ],
            ]),
            'https://api.weather.gov/gridpoints/LWX/96,78/forecast' => Http::response([
                'properties' => [
                    'periods' => [
                        [
                            'name' => 'Tonight',
                            'temperature' => 20,
                            'shortForecast' => 'Partly Cloudy',
                        ],
                    ],
                ],
            ]),
            Weather::getPointEndpoint($user2->latitude, $user2->longitude) => Http::response([
                'properties' => [
                    'forecast' => 'https://api.weather.gov/gridpoints/TOP/31,80/forecast',
                ],
            ]),
            'https://api.weather.gov/gridpoints/TOP/31,80/forecast' => Http::response([
                'properties' => [
                    'periods' => [
                        [
                            'name' => 'Tonight',
                            'temperature' => 30,
                            'shortForecast' => 'Cloudy',
                        ],
                    ],
                ],
            ]),
            Weather::getPointEndpoint($user3->latitude, $user3->longitude) => Http::response([
                "title" => "Unexpected Problem",
                "type" => "https://api.weather.gov/problems/UnexpectedProblem",
                "status" => 500,
                "detail" => "An unexpected problem has occurred. If this error continues, please contact support at nco.ops@noaa.gov.",
            ], 500),
        ]);

        // Running the command
        Artisan::call('weather:forecast');

        // Assert that the user's forecast history has been updated
        $this->assertEquals(1, $user->forecastHistory()->count());
        $this->assertEquals(1, $user2->forecastHistory()->count());
        $this->assertEquals(0, $user3->forecastHistory()->count());

        $userCacheKey = Weather::getCacheKey($user->latitude, $user->longitude);
        $user2CacheKey = Weather::getCacheKey($user2->latitude, $user2->longitude);
        $user3CacheKey = Weather::getCacheKey($user3->latitude, $user3->longitude);

        $this->assertTrue(Cache::has($userCacheKey));
        $this->assertTrue(Cache::has($user2CacheKey));
        $this->assertTrue(Cache::has($user3CacheKey));

        $this->assertEquals(20, Cache::get($userCacheKey)[0]['temperature']);
        $this->assertEquals(30, Cache::get($user2CacheKey)[0]['temperature']);

        $this->assertEquals('Partly Cloudy', Cache::get(Weather::getCacheKey($user->latitude, $user->longitude))[0]['shortForecast']);
        $this->assertEquals('Cloudy', Cache::get(Weather::getCacheKey($user2->latitude, $user2->longitude))[0]['shortForecast']);

        $this->assertEquals([], Weather::getCache($user3->latitude, $user3->longitude));
    }

    public function test_users_endpoint_returns_list_of_users_with_data()
    {
        // Creating test users
        $user1 = User::factory()->create([
            'latitude' => '38.8951',
            'longitude' => '-77.0364',
        ]);
        $user2 = User::factory()->create([
            'latitude' => '26.9391',
            'longitude' => '-99.2646',
        ]);
        $user3 = User::factory()->create([
            'latitude' => '25.7473',
            'longitude' => '-68.6278',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'users')
            ->assertJsonPath('users.0.latitude', '38.8951')
            ->assertJsonPath('users.1.latitude', '26.9391')
            ->assertJsonPath('users.2.latitude', '25.7473');
    }
}
