<?php

namespace App\Services;

use App\Http\Clients\OpenWeatherMapClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeatherForecastService
{

    /** @var OpenWeatherMapClient */
    protected $client;

    /**
     * WeatherForecastService constructor.
     * @param OpenWeatherMapClient $client
     */
    public function __construct(OpenWeatherMapClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $city
     * @return Collection
     */
    public function getForecast(string $city): Collection
    {
        /* only AU city supported */
        $results = $this
            ->client
            ->forecast($city, 'AU');

        $tzOffset = $results['city']['timezone']/60/60;

        return collect($results['list'])
            ->groupBy(function ($day) {
                [$date] = explode(' ', $day['dt_txt']);
                return $date;
            })
            ->map(function (Collection $hours, $dateString) use ($tzOffset) {
                $temps = $hours->pluck('main.temp');
                $humidities = $hours->pluck('main.humidity');

                /* the icon ids increase with weather intensity so we'll
                   take the highest one for the day */
                $weather = $hours
                    ->flatMap
                    ->weather
                    ->reduce(function ($prev, $curr) {
                        if (
                            !$prev
                            || $this->conditionIntensity($curr) >
                            $this->conditionIntensity($prev)
                        ) {
                            $return = $curr;
                        } else {
                            $return = $prev;
                        }

                        return Arr::only($return, ['main', 'icon']);
                    });

                return [
                    'date' => Carbon::parse($dateString, "+{$tzOffset}00"),
                    'temp_max' => $temps->max(),
                    'temp_min' => $temps->min(),
                    'humidity' => $humidities->max(),
                    'weather' => $weather,
                ];
            })
            ->values();
    }

    private function conditionIntensity($condition)
    {
        $matches = [];
        preg_match('/(\d+)/', $condition['icon'], $matches);
        return $matches[1];
    }
}
