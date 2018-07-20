<?php

//flag --what is required using for search word: ex. --what="some_word"
$params = ['w::' => 'what::'];
$options = getopt(implode('', array_keys($params)), $params);

if (!isset($options['what'])) {
	trigger_error("Undefined required argument --what", E_USER_ERROR);
} else {
	$search_string = $options['what'];
}

//Register error_log file
$error_log = 'error_log.log';
if (!file_exists($error_log)) {
	$f = fopen($error_log, 'w');
	fwrite($f, '');
	fclose($f);
}

$urls = [
	'https://pravo.ru',
	'https://ria.ru',
	'https://lenta.ru',
	'https://news.rambler.ru',
	'https://news.yandex.ru'
];

$multi = curl_multi_init();
$channels = [];

foreach ($urls as $url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);

	curl_multi_add_handle($multi, $ch);

	$channels[$url] = $ch;
}

$active = null;
do {
	$status = curl_multi_exec($multi, $active);
	if ($status > 0) {
		$f = fopen($error_log, 'a');
		fwrite($f, curl_multi_strerror($status));
		fclose($f);
	}
} while ($status == CURLM_CALL_MULTI_PERFORM || $active);


$response = [];
foreach ($channels as $key => $channel) {
	$response[$key] = curl_multi_getcontent($channel);
	curl_multi_remove_handle($multi, $channel);
}

curl_multi_close($multi);

if ($response) {
	$result = [];
	foreach ($response as $url => $text) {
		$word_list = str_word_count($text, 1);
		$wc = array_count_values($word_list);
		$result[$url][$search_string] = $wc[$search_string];
	}
}

print_r($result);




