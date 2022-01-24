<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client;

use Exception;
use ReflectionClass;
use Regnet\Helper\Cache;
use Regnet\Helper\Etc;
use Regnet\Helper\Event;
use Regnet\Helper\Event\OnCache;
use const CONTEXT_CLIENT;

/**
 * RPC context
 *
 * @author Adrian Zurkiewicz
 */
class RPC {

	/**
	 *
	 * @var string
	 */
	private $class;

	/**
	 *
	 * @var string
	 */
	private $key;
	
	
	/**
	 * 
	 * @param string $class
	 */
	function __construct(string $class) {
		$this->class = $class;
	}

	/**
	 * 
	 * @param string $method
	 * @return array
	 */
	public function getConfig(string $method): array {

		$client = Etc::get('client', []);

		$classes = @$client['class'];
		$classes = is_array($classes) ? $classes : [];

		$result = @$classes[$this->class];
		$result = is_array($result) ? $result : [];

		if (!$result) {
			return $result;
		}

		if (is_array(@$result['methods'][$method])) {

			foreach ($result['methods'][$method] as $key => $value) {

				$result[$key] = $value;
			}
		}

		unset($result['methods']);

		return $result;
	}

	/**
	 * 
	 * @param string $key
	 * @param type $value
	 * @param int $ttl
	 * @throws Exception
	 */
	public function setCache(string $key, $value, int $ttl) {

		$cache = Event::create(CONTEXT_CLIENT, 'onCache', [OnCache::class]);

		if ($cache) {

			$cache->setCache($key, $value, $ttl);
			return;
		}

		throw new Exception("Configure 'onCache' in etc/client.php");
	}

	/**
	 * 
	 * @param string $key
	 * @param type $value
	 * @return bool
	 * @throws Exception
	 */
	public function getCache(string $key, & $value): bool {

		$cache = Event::create(CONTEXT_CLIENT, 'onCache', [OnCache::class]);

		if ($cache) {
			return $cache->getCache($key, $value);
		}

		throw new Exception("Configure 'onCache' in etc/client.php");
	}

	/**
	 * Set API key
	 * 
	 * @param string $key
	 * @return void
	 */
	function setKey(string $key): void {
		$this->key = $key;
	}

	/**
	 * 
	 * @return string|null
	 */
	function getKey(): ?string {
		return $this->key;
	}

}
