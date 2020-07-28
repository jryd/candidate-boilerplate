<?php

namespace Tests\Unit\Services;

use App\Http\Clients\OpenWeatherMapClient;
use App\Services\WeatherForecastService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WeatherForecastServiceTest extends TestCase
{

    /** @test */
    public function can_get_forecast()
    {
        Log::setDefaultDriver('errorlog');

        $city = 'Brisbane';
        $clientMock = \Mockery::mock(OpenWeatherMapClient::class)
            ->expects('forecast')
            ->withArgs([$city, 'AU'])
            ->andReturn(json_decode(file_get_contents(
                __DIR__.'/fixture/forecast_data.json'
            ), true))
            ->getMock();

        app()->instance(OpenWeatherMapClient::class, $clientMock);

        $service = app(WeatherForecastService::class);
        $forecast = $service->getForecast($city);

        $this->assertCount(6, $forecast);
        $this->assertEquals(10, $forecast->first()['date']->offsetHours);
        $this->assertKeys(
            ['temp_min', 'temp_max', 'humidity', 'weather', 'date'],
            $forecast->first()
        );
    }

    /**
     * @param $expectedKeys
     * @param $actual
     */
    private function assertKeys($expectedKeys, $actual): void
    {
        $actualKeys = array_keys($actual);

        sort($expectedKeys);
        sort($actualKeys);

        $this->assertEquals($expectedKeys, $actualKeys);
    }
}
