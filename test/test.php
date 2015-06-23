<?php
require_once 'vendor/autoload.php';

$client = new Prometheus\Client;

$counter = $client->newCounter([
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

var_dump($client->debugStats());

