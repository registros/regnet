<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Server;

use Exception;
use ReflectionClass;
use Regnet\Helper\Etc;
use Regnet\Helper\Event;
use Regnet\Helper\Event\OnCache;
use Regnet\Server\Event\OnRequest;
use Throwable;
use const CONTEXT_SERVER;

/**
 * Allows external calls (RPC) to local classes.
 *
 * @author Adrian Zurkiewicz
 */
class ClassAPI {

	/**
	 *
	 * @var Request
	 */
	private $request;

	/**
	 *
	 * @var Response
	 */
	private $response;

	/**
	 *
	 * @var int|false 
	 */
	private $cache = false;

	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	function __construct(Request $request = null, Response $response = null) {
		$this->request = $request ? $request : new Request();
		$this->response = $response ? $response : new Response($this->request);
	}

	/**
	 * 
	 * @param ReflectionClass $reflection
	 * @throws Exception
	 */
	public function onRPC(ReflectionClass $reflection) {
		
		$event = Event::create(CONTEXT_SERVER, 'onRequest', [OnRequest::class]);
		
		if ($event) {
			
			$event->onRPC($this->request, $reflection);
		}
		
	}

	/**
	 * 
	 * @param string $key
	 * @param type $value
	 * @param int $ttl
	 * @throws Exception
	 */
	private function setCache(string $key, $value, int $ttl) {

		$cache = Event::create(CONTEXT_SERVER, 'onCache', [OnCache::class]);

		if ($cache) {

			$cache->setCache($key, $value, $ttl);
			return;
		}


		throw new Exception("Configure 'onCache' in local/etc/server.php");
	}

	/**
	 * 
	 * @param string $key
	 * @param type $value
	 * @return bool
	 * @throws Exception
	 */
	private function getCache(string $key, & $value): bool {

		$cache = Event::create(CONTEXT_SERVER, 'onCache', [OnCache::class]);

		if ($cache) {
			return $cache->getCache($key, $value);
		}
		
		throw new Exception("Configure 'onCache' in local/etc/server.php");
	}

	/**
	 * 
	 * @param int $ttl
	 */
	public function activeCache(int $ttl) {

		$this->cache = $ttl;
	}

	/**
	 * 
	 */
	public function run() {

		try {

			$this->response->setResult($this->call());
		} catch (Throwable $exc) {
			$this->response->setException($exc);
		}
	}

	/**
	 * 
	 * @return mixed
	 * @throws Exception
	 */
	public function call() {

		$input = $this->request->getInput();

		if (!isset($input['rpc'])) {
			throw new Exception("This is not a RPC request");
		}

		$call = $input['data'];

		if ($this->cache) {

			$cache_key = sha1(json_encode($call) . __FUNCTION__);

			if ($this->getCache($cache_key, $result)) {
				return $result;
			}
		}


		$class = @$call['class'];
		$constructor = @$call['constructor'];
		$method = @$call['method'];
		$arguments = @$call['arguments'];

		if (!$class) {
			throw new Exception("The class name was not specified");
		}

		if (!$method) {
			throw new Exception("The method name was not specified");
		}

		if (!class_exists($class)) {
			throw new Exception("Class not exists: '{$class}'");
		}

		$reflection = new ReflectionClass($class);

		$this->onRPC($reflection);

		if ($constructor) {

			$object = $reflection->newInstanceArgs($constructor);
		} else {
			$object = $reflection->newInstance();
		}

		if (!is_callable([$object, $method])) {

			throw new Exception("Cannot call method: {$class}::{$method}()");
		}

		if ($arguments) {

			$result = call_user_func_array([$object, $method], $arguments);
		} else {

			$result = call_user_func([$object, $method]);
		}

		if ($this->cache) {

			$this->setCache($cache_key, $result, (int) $this->cache);
		}

		return $result;
	}

	/**
	 * 
	 */
	public function dump() {

		if (!$this->response->isReady()) {

			$this->run();
		}

		$this->response->dump();
	}

	/**
	 * 
	 * @return array
	 */
	public function getData() {

		if (!$this->response->isReady()) {

			$this->run();
		}

		return $this->response->getData();
	}

}
