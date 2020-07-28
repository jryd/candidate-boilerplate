<?php

namespace App\Http\Controllers;

use App\Http\Clients\Exceptions\LocationNotfoundException;
use App\Services\WeatherForecastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

class ForecastController extends Controller
{

    /** @var WeatherForecastService */
    protected $forecastService;

    /**
     * ForecastController constructor.
     * @param WeatherForecastService $forecastService
     */
    public function __construct(WeatherForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * @return JsonResponse
     */
    public function index(string $city): JsonResponse
    {
        try {
            $forecast = $this->forecastService->getForecast($city);
        } catch (LocationNotfoundException $exception) {
            return response('Unknown location ' . $city, 404);
        }
        return response()->json($forecast);
    }
}
