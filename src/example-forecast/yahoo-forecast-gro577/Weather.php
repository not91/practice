<?php
class YahooTimeoutException extends Exception {
    public function __construct()
    {
        echo "Сервер не ответил вовремя";
    }
}

class Client
{
    private $city;
    private $date;

    public function __construct($city)
    {
        $this->date = date("j M Y");
        $this->city = $city;
        $this->getCity();
    }

    public function getCity()
    {
        $BASE_URL = "http://query.yahooapis.com/v1/public/yql";
        $yahoo_query = "select item.yweather:condition.temp from weather.forecast where woeid in (select woeid from geo.places(1) where text='$this->city') and u='c'";
        $yahoo_query_url = $BASE_URL . "?q=" . urlencode($yahoo_query) . "&format=json";
        $rastr = curl_init($yahoo_query_url);
        curl_setopt($rastr, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rastr, CURLOPT_TIMEOUT, 10);

        if (curl_exec($rastr) === false && curl_errno($rastr)==28) {
                throw new YahooTimeoutException();
        } else {
            $json = curl_exec($rastr);
            curl_close($rastr);
            $result = json_decode($json);
            $weather=[$this->date=>$this->findTemp($result)];
            return $weather;
        }
    }
    private function findTemp($array){
        $result="";
        foreach ($array as $key=>$item){
            if (is_array($item) || is_object($item)){
                $result=$this->findTemp($item);
            }
            elseif($key=="temp"){
                return $item;
            }
        }
        return $result;
    }
}


$b = new Client("pyatigorsk");




