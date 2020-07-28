<?php

namespace App\Console\Commands;

use App\Http\Clients\Exceptions\LocationNotfoundException;
use App\Services\WeatherForecastService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class WeatherForecastCommand extends Command
{

    protected $signature = <<<SIG
        forecast:city {--cities=} 
SIG;

    public function handle(WeatherForecastService $forecastService)
    {
        $cities = explode(',', $this->option('cities'));

        if (!is_array($cities)) {
            $cities = [$cities];
        }

        collect($cities)
            ->tap(function (Collection $cities) {
                $count = $cities->count();
                $this->info("Getting forecast for $count cities");
            })
            ->each(function ($city) use ($forecastService) {
                try {
                    $forecast = $forecastService->getForecast($city);
                } catch (LocationNotfoundException $exception) {
                    $this->error($exception);
                    return;
                } catch (\Throwable $throwable) {
                    Log::error(
                        'Error getting forecast for city ' . $city,
                        [ 'exception' => $throwable ]
                    );
                    $this->warn('Forecast not available for city '.$city);
                    return;
                }
                $headers = $forecast
                    ->pluck('date.englishDayOfWeek')
                    ->prepend($city);
                $body = [
                    $forecast->pluck('temp_min')->prepend('Temp Min')->all(),
                    $forecast->pluck('temp_max')->prepend('Temp Max')->all(),
                    $forecast->pluck('humidity')->prepend('Humidity')->all(),
                    $forecast->pluck('weather.main')->prepend('Weather')->all(),
                ];

                $this->info('Forecast for ' . $city);
                $this->table($headers, $body);
            });


    }
}
