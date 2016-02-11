<?php

require_once dirname(__FILE__) . '/../src/Client.php';


$client = new Prometheus\Client([
	'base_uri' => 'http://localhost:9091',
]);

$counter = $client->newCounter([
	'namespace' => 'louddoor',
	'subsystem' => 'promotions',
	'name' => 'TestCounter',
	'help' => 'Some testing bullshit',
]);

$job_id = uniqid();
while(true)
{

	$counter->increment( [ 'promo' => 1 ], rand( 1, 50 ) );
	$counter->increment( [ 'promo' => 2 ], rand( 1, 50 ) );
	$counter->increment( [ 'promo' => 3 ], rand( 1, 50 ) );

	$data = $client->getStats(); # we may not need to serialize the data first.


	echo "http://localhost:9091/metrics/job/".$job_id."\n";

	$ch = curl_init( "http://localhost:9091/metrics/job/".$job_id );

	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

	$response = curl_exec( $ch );
	if ( !$response )
	{
		echo "failed\n";
	}

	print_r( $response );

	$sleepTime = rand(1,20);
	echo "sleeping $sleepTime\n";
	sleep($sleepTime);
}