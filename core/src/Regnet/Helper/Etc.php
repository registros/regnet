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
use const REGNET_LOCAL_DIR;

/**
 * Etc helper
 *
 * @author Adrian Zurkiewicz
 */
class Etc {
	
	/**
	 *
	 * @var array
	 */
	static private $data = [];	
	
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return array
	 * @throws Exception
	 */
	static public function get(string $name, $default = null): array {
		
		if (is_array(@self::$data[$name])) {
			
			return self::$data[$name];
		}
		
		$file = REGNET_LOCAL_DIR . "/local/etc/{$name}.php";
		
		if (!file_exists($file)) {
			
			if (func_num_args() > 1) {
				return $default;
			}
			
			throw new Exception("Configuration file not exist ({$file}). \nTry to run " . REGNET_LOCAL_DIR . "/regnet.php");
		}
		
		$result = include $file;
		
		self::$data[$name] = is_array($result) ? $result : [];
		
		return self::$data[$name];
	}
	
}
