<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Helper;

use Exception;
use ReflectionClass;

/**
 * Handler of events
 *
 * @author Adrian Zurkiewicz
 */
class Event {
	
	/**
	 * Create an events object
	 * 
	 * @param string $context
	 * @param string $event
	 * @param array $interfaces
	 * @return object|null
	 * @throws Exception
	 */
	static public function create(string $context, string $event, array $interfaces = []): ?object {
		
		$config = Etc::get($context, []);
		$result = null;
		
		if (is_array(@$config['event'])) {
			
			$result = @$config['event'][$event];
						
		}
		
		if (!$result) {
			return null;
		}		
				
		$rc = new ReflectionClass($result);
		
		foreach ($interfaces as $interface) {
			
			if (!$rc->implementsInterface($interface)) {
				throw new Exception('Event must implement interface: ' . $interface);
			}
		}
		
		
		return $rc->newInstance();
	}
	
	
}
