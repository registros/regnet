<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */
use Test\Test;
use Test\Test2;
use Test\Test3;

return [
	/*
	 * List of CIDR
	 */
	'mynetwork' => ['127.0.0.0/24', '192.168.0.0/16'],
	/*
	 * Events
	 */
	'event' => [
	/*
	 * Called when client needs to use cache
	 * 
	 * Event onCache must implement interface: Regnet\Helper\Event\OnCache
	 */
//		'onCache' => Cache::class,

	/*
	 * Called when the server receives a request
	 * 
	 * Event onRequest must implement interface: Regnet\Server\Event\OnRequest
	 */
//		'onRequest' => ServerRequest::class,

	/*
	 * When the server sends a response
	 * 
	 * Event onResponse must implement interface: Regnet\Server\Event\OnResponse
	 */
//		'onResponse' => ServerResponse::class,
	],
	/*
	 * Information about classes that will be exported through API.
	 */
	'export' => [
		Test::class => [
			'mynetwork' => false, // Limit acces to my network (default: true). Set it to false to be able to access from all networks.
		],
		Test2::class => true, // Use default options (false to disabled class)
		Test3::class => [
			'access' => true, // Default: true
//			'key' => 'abc', // Default: disabled
//			'network' => ['127.0.0.0/24', '192.168.0.0/16'], // Limit acces to determined networks. Default: [] 
//			'cache' => 10, // Active server side cache with 10 second of TTL
			'methods' => [
				'myInternalFunction' => false,
				'get' => [
					'mynetwork' => false,
//					'network' => ['127.0.0.0/24', '192.168.0.0/16'], // Limit acces to determined networks. Default: [] 
//					'cache' => false, // Disable cache for this method
				],
				'myRestrictedMethod1' => [
					'mynetwork' => true,
				],
				'myRestrictedMethod2' => [
					'network' => ['127.0.0.0/24', '192.168.0.0/16'],
				],
				'myRestrictedMethod2' => [
					'key' => 'abc',
				],
			],
		],
	],
];
