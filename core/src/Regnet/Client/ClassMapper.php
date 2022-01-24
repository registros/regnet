<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client;

use Regnet\Client\Event\OnRequest;
use Regnet\Helper\Event;
use const CONTEXT_CLIENT;

/**
 * Handler of remote method call (RPC).
 *
 * @author Adrian Zurkiewicz
 */
class ClassMapper {

	/**
	 *
	 * @var string
	 */
	private $url;

	/**
	 *
	 * @var string
	 */
	private $class;

	/**
	 *
	 * @var array 
	 */
	private $constructor;

	/**
	 *
	 * @var Client
	 */
	private $client;

	/**
	 *
	 * @var RPC
	 */
	public $rpc;

	/**
	 * 
	 * @param string $url
	 * @param string $class
	 * @param mixed $_ Constructor parameters
	 */
	function __construct(string $url, string $class, $_ = null) {
		$this->url = $url;
		$this->class = $class;

		$params = func_get_args();
		array_shift($params);
		array_shift($params);

		$this->constructor = $params;
		$this->client = new Client();
		$this->rpc = new RPC(get_called_class());
	}

	/**
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {

		$config = $this->rpc->getConfig($name);
//		debug($config);

		$ttl = (int) @$config['cache'];
		
		if (@$config['key']) {
			$this->rpc->setKey($config['key']);
		}
		
//		debug($ttl);

		$call = [
			'class' => $this->class,
			'constructor' => $this->constructor,
			'method' => $name,
			'arguments' => $arguments,
		];

		if ($ttl) {

			$key = sha1(json_encode($call) . __METHOD__);

			if ($this->rpc->getCache($key, $result)) {
				return $result;
			}
		}

		$request = new Request($this->url, $call, $this->rpc->getKey());
		$request->setAttribute('rpc', '1.0');
		$request->setHeader(ACTION_HEADER, ACTION_RPC);

		$event = Event::create(CONTEXT_CLIENT, 'onRequest', [OnRequest::class]);

		if ($event) {

			$event->onRPC($request, $this);
		}

		$result = $this->client->request($request);

		if ($ttl) {

			$this->rpc->setCache($key, $result, $ttl);
		}

		return $result;
	}

}
