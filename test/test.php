<?php
require_once 'vendor/autoload.php';

$client = new Prometheus\Client([
	'base_uri' => 'http://condor.louddev.com:9091',
]);

$counter = $client->newCounter([
	'namespace' => 'louddoor',
	'subsystem' => 'promotions',
	'name' => 'TestCounter',
	'help' => 'Some testing bullshit',
]);

$counter->increment(['promo' => 1]);
$counter->increment(['promo' => 1]);
$counter->increment(['promo' => 1]);
$counter->increment(['promo' => 1]);

$counter->increment(['promo' => 2]);
$counter->increment(['promo' => 2]);

$counter->increment(['promo' => 3]);
$counter->increment(['promo' => 4]);

$client->sendStats();

