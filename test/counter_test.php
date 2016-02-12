<?php

require_once dirname(__FILE__) . '/../src/Client.php';


$client = new Prometheus\Client([
	'base_uri' => 'http://localhost:9091/metrics/job/',
]);

$counter = $client->newCounter([
	'namespace' => 'php_client',
	'subsystem' => 'testing',
	'name' => 'counter',
	'help' => 'Testing the PHPClients Counter',
]);

$job_id = uniqid();
while(true)
{
	$counter->increment( [ 'url' => 'home.php', 'status_code' => 200 ], rand( 1, 50 ) );
	$counter->increment( [ 'url' => 'home.php', 'status_code' => 404 ], rand( 1, 50 ) );
	
	$client->pushMetrics( "pretend_server", $job_id );

	$sleepTime = rand( 1, 20 );
	echo "sleeping $sleepTime\n";
	sleep( $sleepTime );
}
