<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Helper\Event;

/**
 * Call on cache
 *
 * @author Adrian Zurkiewicz
 */
interface OnCache {

	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 */
	public function setCache(string $key, $value, int $ttl);

	/**
	 * 
	 * @param string $key
	 * @param mixed $value Container for cached value
	 * @return bool Returns true if the cache has been hit
	 */
	public function getCache(string $key, & $value): bool;		
	
}
