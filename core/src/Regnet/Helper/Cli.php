<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Helper;

use Throwable;
use const DEBUG;

/**
 * Cli helper
 *
 * @author Adrian Zurkiewicz
 */
class Cli {
	
	
	/**
	 * 
	 */
	const COLOR_YELLOW = '1;33';	

	/**
	 * 
	 */
	const COLOR_RED = '1;31';

	/**
	 * 
	 */
	const COLOR_GREEN = '1;32';

	/**
	 * 
	 * @param type $data
	 * @param type $color
	 * @return type
	 */
	static function output($data, $color = null) {

		if ($data instanceof Throwable) {

			$message = $data->getMessage();
			self::output($message, self::COLOR_RED);

			if (DEBUG) {
				echo (string) $data, "\n";
			} else {

				self::output('Use the -d option to display more information');
			}

			return;
		}


		if (is_numeric($data)) {

			$data = (string) $data;
		}

		if (is_bool($data)) {
			$data = $data ? 'true' : 'false';
		}

		if (is_string($data)) {

			if ($color) {
				$data = "\e[{$color}m{$data}\e[0m";
			}

			echo $data, "\n";
			return;
		}

		print_r($data);
		echo "\n";
	}

	/**
	 * 
	 * @param mixed $data
	 */
	static function debug($data) {

		if ((is_numeric($data)) or (is_bool($data))) {
			var_dump($data);
			exit;
		}

		self::output($data);
		exit;
	}

	/**
	 * 
	 * @param string $description
	 * @param bool $required
	 * @param string $default
	 * @return string
	 */
	static public function input(string $description, bool $required = true, string $default = ''): string {

		$default_info = null;

		if ($default = trim($default)) {

			$default_info = "[default: {$default}] ";
		}


		$result = trim(readline("{$description} {$default_info}: "));

		if ((!$result) and ($default)) {
			$result = $default;
		}

		if (($required) and (!$result)) {
			$result = self::input($description, $required, $default);
		}

		return $result;
	}

	/**
	 * 
	 * @param string $description
	 * @param array $options
	 * @param string $default
	 * @return string
	 */
	static public function choice(string $description, array $options, string $default = ''): string {

		$result = '';
		$permitted = array_keys($options);

		self::output(null);
		self::output($description . ':');
		self::output(null);

		foreach ($options as $key => $value) {

			self::output(" [{$key}] : {$value}");
		}

		self::output(null);

		$description = 'Type [' . implode('/', $permitted) . ']';

		while (!in_array($result, $permitted)) {
			$result = self::input($description, true, $default);
		}

		return $result;
	}

}
