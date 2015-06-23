<?php
require_once 'vendor/autoload.php';

$client = new Prometheus\Client;

$counter = $client->newCounter([
	'name' => 'TestCounter',
	'help' => 'Some testing bullshit',
]);

if (preg_match('/\/metrics$/', $_SERVER["REQUEST_URI"])) {
	$client->renderStats();
} else {
    $counter->increment($_GET);
}
