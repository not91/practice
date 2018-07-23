<?php
$givenArguments = getopt("", array("what:"));
if(empty($givenArguments)){
    trigger_error("заполните аргумент what", E_USER_ERROR);
}
$what=mb_strtolower($givenArguments['what']);
$urls = [
    'https://pravo.ru',
    'https://ria.ru',
    'https://lenta.ru',
    'https://news.rambler.ru',
    'https://news.yandex.ru'
];

$sources=array();
$curl_container = curl_multi_init();
foreach ($urls as $url){
    $curl_source=curl_init($url);
    //curl_setopt($curl_source,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curl_source, CURLOPT_HEADER, 0);
    curl_setopt($curl_source, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_source, CURLOPT_TIMEOUT, 15);
    curl_multi_add_handle($curl_container,$curl_source);
    array_push($sources, $curl_source);
}

$active = null;
//запускаем дескрипторы
do {
    $mrc = curl_multi_exec($curl_container, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

$result=array();
foreach ($sources as $source){
    $page_string=mb_strtolower(curl_multi_getcontent($source));
    if(preg_match("/(\W)$what(?![a-zа-я0-9])/",$page_string))
    {
        $count=(substr_count($page_string,$what)) ;
        $result[curl_getinfo ( $source ,CURLINFO_EFFECTIVE_URL )]=$count;
    }

}

curl_multi_close($curl_container);
var_dump($result);