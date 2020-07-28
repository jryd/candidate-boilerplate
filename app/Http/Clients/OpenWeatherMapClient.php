<?php

namespace App\Http\Clients;

use App\Http\Clients\Exceptions\LocationNotfoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\Psr7\parse_query;

/**
 * Class OpenWeatherMapClient
 * @package App\Http\Clients
 */
class OpenWeatherMapClient
{

    protected const API_URI = 'https://api.openweathermap.org/data/';

    /** @var Client */
    protected $client;

    /**
     * OpenWeatherMapClient constructor.
     */
    public function __construct()
    {
        $version = config('services.openweathermap.version');
        $apiKey = config('services.openweathermap.api_key');

        $client = new Client([
            'base_uri' => self::API_URI . $version . '/',
        ]);

        /* add default params */
        /** @var HandlerStack $stack */
        $stack = $client->getConfig('handler');
        $stack->unshift(
            Middleware::mapRequest(function (
                RequestInterface $request
            ) use (
                $apiKey
            ) {
                $params = parse_query($request->getUri()->getQuery());
                $params['mode'] = 'json';
                $params['units'] = 'metric';
                $params['APPID'] = $apiKey;

                $query = http_build_query($params);

                return $request
                    ->withUri(
                        $request
                            ->getUri()
                            ->withQuery(
                                $query
                            )
                    )
                    ->withHeader(
                        'Content-Type',
                        'application/json'
                    );
        }));
        if (config('services.openweathermap.logging_enabled')) {
            $stack->push(Middleware::log(
                Log::getFacadeRoot(),
                new MessageFormatter(MessageFormatter::DEBUG)
            ));
        }
        $this->client = $client;
    }

    public function forecast(string $city, string $country)
    {
        try {
            $response = $this->client->get('forecast', [
                'query' => ['q' => "$city,$country"],
            ]);
        } catch (ClientException $clientException) {
            $response = $clientException
                ->getResponse();
            if ($response
                && (int) $response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $response->getBody()->rewind();
                $data = json_decode($response->getBody()->getContents(), true);
                throw new LocationNotfoundException($data['message'] ?? 'Not found');
            }

            throw $clientException;
        }

        $response->getBody()->rewind();
        return json_decode($response->getBody()->getContents(), true);
    }
}
