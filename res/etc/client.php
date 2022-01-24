<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */


return [
	
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
		 * Called when the client sends a request
		 * 
		 * Event onRequest must implement interface: Regnet\Client\Event\OnRequest
		 */
//		'onRequest' => ClientRequest::class,	
		
		/*
		 * When the client receives a response
		 *  
		 * Event onResponse must implement interface: Regnet\Client\Event\OnResponse
		 */
//		'onResponse' => ClientResponse::class,	
		
	],
	
	/*
	 * You have to use the local names of the classes.
	 * 
	 */
	'class' => [
//		Test2::class => [
//			'cache' => 5,	// TTL in seconds: Active cache on the client side 
//			'key' => 'abc', // Set api key for this class. This parameter can be set in your code after creating the instance of class ($class->rpc->setApi();).
//			'methods' => [
//				'get' => [
//					'cache' => false,
//				],
//			],
//		],
	],
	
	/*
	 * You have to use the local namespace.
	 * 
	 */	
	'namespace' => [
//		'Regnet\\Sample' => [
//			'key' => 'abc', // Set api key for this class.
//		],
	],	
];
