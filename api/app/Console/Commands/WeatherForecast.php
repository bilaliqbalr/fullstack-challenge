<?php

namespace App\Console\Commands;

use App\Exceptions\WeatherHttpException;
use App\Models\User;
use App\Services\Weather;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class WeatherForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will get weather forecast from API and store info into DB and Cache';

    /**
     * Execute the console command.
     */
    public function handle(): void {

        // Getting users in chunks to avoid memory & performance issues
        User::chunk(20, function (Collection $users) {
            $users->each(function ($user) {
                // There we'll check if we got proper API response then store in Cache and DB (for history)
                // else, need to retry for 3 times to get response in case of API failure
                $retries = 1;
                do {
                    $status = true;
                    $this->line("Getting API data for user {$user->id} & retry #$retries");

                    try {
                        (new Weather($user))->save();

                    } catch (WeatherHttpException $e) {
                        $status = false;
                        $retries++;

                        $this->line("API failed");

                        // Retrying call after few seconds in case of error
                        sleep(mt_rand(1, 5));
                    }

                } while ($status === false && $retries <= 3);
            });
        });
    }
}
