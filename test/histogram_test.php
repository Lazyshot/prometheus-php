<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';


$client = new Prometheus\Client([
	'base_uri' => 'http://localhost:9091/metrics/job/',
]);

$histogram = $client->newHistogram([
	'namespace' => 'meta_data',
	'subsystem' => 'tv',
	'name' => 'elements_per_hit',
	'help' => 'Testing the PHPClients Histogram',
	'buckets' => [10, 25, 50, 75, 100]
]);

while(true){
	$histogram->observe(['domain'=>'hulu'], rand(0,100));
	$histogram->observe(['domain'=>'crunchyroll'], rand(0,100));

	$client->pushMetrics( "foreign_language", "test_server_0");

	echo "sleeping 5\n";
	sleep(5);
}



