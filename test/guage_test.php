<?php

require_once dirname(__FILE__) . '/../src/Client.php';


$client = new Prometheus\Client([
	'base_uri' => 'http://localhost:9091/metrics/job/',
]);

$guage = $client->newGauge([
	'namespace' => 'php_client',
	'subsystem' => 'testing',
	'name' => 'Guage',
	'help' => 'Testing the PHPClients guage',
]);

$job_id = uniqid();
while(true)
{
	$guage->set( [ 'key1' => 'val1' ], rand( 1, 50 ) );
	$guage->set( [ 'key2' => 'val2'], rand( 1, 50 ) );

	$client->pushMetrics( "pretend_server", $job_id );

	$sleepTime = rand( 1, 20 );
	echo "sleeping $sleepTime\n";
	sleep( $sleepTime );
}
