<?php

interface Forecast
{
    public function getData($city) : array;
}

class YahooForecast implements Forecast
{
    private $_cache = [];

    private $whoid_list = [
        'moscow' => 2122265
    ];

    public function getData($city) : array
    {
        try {
            if (!isset($this->whoid_list[$city])) {
                throw new ForecastException('Undefined whoid for' . $city);
            }
            $whoid = $this->whoid_list[$city];
            if (!array_key_exists($whoid, $this->_cache)) {
                $url = "https://query.yahooapis.com/v1/public/yql?q=" .
                    "select%20item.condition%20from%20weather.forecast%20where%20woeid%20%3D%20" . "'$whoid'" .
                    "%20and%20u%3D'c'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
                $ctx = stream_context_create(['http' => ['timeout' => 25]]);
                $response = file_get_contents($url, false, $ctx);
                $forecast = json_decode($response, true);
                $date = $forecast['query']['results']['channel']['item']['condition']['date'];
                $temp = $forecast['query']['results']['channel']['item']['condition']['temp'];
                $this->_cache[$whoid] = ['date' => $date, 'temp' => $temp];
            }
            return $this->_cache[$whoid];

        } catch (ForecastException $ex) {
            return ['code' => $ex->getCode(), 'message' => $ex->getMessage()];
        } catch (Exception $ex) {
            return ['code' => 0, 'message' => 'request error.'];
        }
    }
}

class ForecastException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        if (!$message) {
            $message = 'Forecast error.';
        }
        parent::__construct($message, $code, $previous);
    }
}

class Client
{
    private $_forecast = null;

    public function __construct(Forecast $forecast)
    {
        $this->_forecast = $forecast;
    }

    public function getForecast($city = 'moscow')
    {
        return $this->_forecast->getData($city);
    }
}

/*
How to use:

$yahoo = new YahooForecast();
$client = new Client($yahoo);
$data = $client->getForecast();
print_r($data);
 */
