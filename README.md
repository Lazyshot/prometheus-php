PHP Client For Prometheus
=========================

An unofficial client for Prometheus (http://prometheus.io/) written in PHP. 
This fork is nearly identical to @TimeZynk fork except it includes a test script for both the counter and histogram.
In addition a function for pushing the serialized client data to a prometheus push gateway with curl is provided.
Lastly setup instructions for Prometheus, a Prometheus push gateway, and some example queries are provided with links.

*Note* It is strongly recommended that you read through some of the Prometheus documentation if you haven't already.

https://prometheus.io/docs/introduction/getting_started/


Requirements
------------

A Prometheus server running on the localhost running with the provided .yml file.
A Prometheus push gateway running on the localhost listening on port 9091.
A php installation with the curl extension enabled... look up how to do this if you don't know.


Setup Prometheus
================

Lets launch the prometheus server so that it listens only to the push gateway that we will setup in the next step.
We purposefully don't have prometheus listen to itself so it's easier to find the data we send to it later.

- Place the "example_config.yml" file next to where you have the Prometheus server installed.
- If Prometheus is running then kill the server.
- **OPTIONAL** remove the /data/ folder where prometheus is storing it's data.  Only do this if you want to remove any previous
testing data you have done so far.
- Run this command.  
	./prometheus -config.file=no_prom.yml

Prometheus is now running and is configured to pull from the localhost at port 9091.  Let's give it something to pull!


Setup Prometheus Push Gateway
==============================

The Push Gateway allows short lived applications that would otherwise be a pain to try and pull data from to still be tracked.
In addition it simplifies the task of sending the data we want to track to Prometheus at relatively minor cost.
The prometheus documentation covers the advantages of PULL vs PUSH logging.

*Push Gateway Link* https://github.com/prometheus/pushgateway

- Follow the instructions at the prometheus pushgateway git to get the gateway installed and running on localhost.
- That's it!


Supported Metrics
=================

Before explaining how to use the client it's important to understand what metrics the PHP client supports.

- *Counter* This metric can only be incremented positevely by 1 or more.
- *Gauge* This metric can be incremented both postively and negatively. This has some drawbacks in limiting the usefullnes
 of certain querying functions.
- *Histogram* This metric is a collection of buckets that counts how many data points fell into each bucket
 and a sum of all the values of those data points.
- Summaries are not supported.

It's highly recommended to view the Prometheus documentations "Concepts => Data Model" to learn about the intricacies these metrics.


Using PHP Push Gateway Client
==============================

This fork has tried to simplify the process of using the client so that no outside libraries are required and everything should work
right out of the box.

The only file that we need to include in order to start using the client is the Client.php.
```php
require_once dirname(__FILE__) . '/../src/Client.php';
```

Creating a new client is easy.  Since the PHP client lives in the Prometheus namespace we must include that when creating
a new client.  In addition the client must be passed a list of options.  Currently the only valid option is 'base_uri'.
If you don't plan on using the built in "pushMetrics" function, you may set this to an empty string.
```php
$client = new Prometheus\Client('http://localhost:9091/metrics/job/');
```

Next we tell the client to create a new metric.  Here we are creating a new *Counter*.
```php
	$counter = $client->newCounter([
		'namespace' => 'php_client',
		'subsystem' => 'testing',
		'name' => 'counter',
		'help' => 'Testing the PHPClients Counter',
	]);
```

We can use the new counter to increment different things.  Here we pretend to be counting the status_codes returned by an
imaginary server to clients for the "home.php" page.
```php
	$counter->increment( [ 'url' => 'home.php', 'status_code' => 200 ], rand( 1, 50 ) );
	$counter->increment( [ 'url' => 'home.php', 'status_code' => 404 ], rand( 1, 50 ) );
```

Once we have gathered enough data we tell the client to send the metrics to the Prometheus Push Gateway.
We can either send the data right to the gateway.  Or we may specify a job, or a job and an instance of that job.
The documentation at the Prometheus Push Gateway Git covers the specifics of what happens when setting jobs
and instances and will not be covered here.
```php
	$client->pushMetrics( "pretend_server", $job_id );
```

Here is the above code all in one snippet.

```php
	require_once dirname(__FILE__) . '/../src/Client.php';

	$client = new Prometheus\Client('http://localhost:9091/metrics/job/');

	$counter = $client->newCounter([
		'namespace' => 'php_client',
		'subsystem' => 'testing',
		'name' => 'counter',
		'help' => 'Testing the PHPClients Counter',
	]);

	$counter->increment( [ 'url' => 'home.php', 'status_code' => 200 ], rand( 1, 50 ) );
	$counter->increment( [ 'url' => 'home.php', 'status_code' => 404 ], rand( 1, 50 ) );

	$client->pushMetrics( "pretend_server", "test_instance" );
```
 
Going Further
=============

Go ahead and run the /test/histogram_test.php function for a minute or two.  The output is mundane so go get some coffee.
Now you can navigate to the "http://localhost:9090/graph" in a web browser and execute the following query.

rate(meta_data_tv_elements_per_hit_sum[5m]) / rate(meta_data_tv_elements_per_hit_count[5m]) 

Adjust the graph to show over the past 15m.  This is now a graph that would accurately show how many elements per hit an
imaginary web scraper is pulling from from two different domains. 


Just Serialized Data
=====================

If you want to see the data that is being sent to the server so you can expose it through a server or do whatever you wish
you can simply call the serialize() function from the client.
```php
	echo $client->serialize();
```



