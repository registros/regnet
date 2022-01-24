<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Server\Explorer;

/**
 * Docblock helper
 *
 * @author Adrian Zurkiewicz
 */
class Docblock {
	
	
	
	/**
	 * 
	 * @param string $string
	 * @return array
	 */
	private function parseParamLine(string $string): array {
		
//		debug($string);

		$result = [];
		$param = ltrim($string, '@');
		$param = explode(' ', $param, 4);

		if (@$param[2]) {
			$result['name'] = $param[2];
			$result['name'] = trim($result['name'], '$');
		}

		if (@$param[1]) {
			$result['type'] = $param[1];
		}

		if (@$param[3]) {
			$result['description'] = $param[3];
		}

//		debug($result);
		

		return $result;
	}

	/**
	 * 
	 * @param string $string
	 * @return array
	 */
	private function parseReturnLin(string $string): array {

		$result = [];
		$param = ltrim($string, '@');
		$param = explode(' ', $param, 3);

		if (@$param[1]) {
			$result['type'] = $param[1];
			
			if ($result['type'] == 'boolean') {
				$result['type'] = 'bool';
			}
			
		}


		if (@$param[2]) {
			$result['description'] = $param[2];
		}

		return $result;
	}

	/**
	 * 
	 * @param string $docblock
	 * @param string $class
	 * @param string $method
	 * @return array
	 */
	public function parse(string $docblock, string $class, string $method = null): array {

		$result = [];
		$description = [];
		$tags = [];
		$skip = false;
		$params = [];
		$return = null;

		$docblock = explode("\n", $docblock);

		foreach ($docblock as $line) {

			$line = preg_replace('/\s+/', ' ', $line);
			$line = trim($line);

			if (in_array($line, ['/**', '*/'])) {
				continue;
			}

			$line = ltrim($line, '* ');
			$line = trim($line);

			if (!$line) {
				continue;
			}


			if ($line == '<code>') {
				$skip = true;
				continue;
			}

			if ($line == '</code>') {
				$skip = false;
				continue;
			}


			if ($skip) {
				continue;
			}


			if (strpos($line, '@') === 0) {

				$tag = ltrim($line, '@');
				$tag = explode(' ', $tag, 2);

				if (!@$tag[1]) {
					$tag[1] = null;
				}

				if ($tag[0] == 'param') {

					$param = $this->parseParamLine($line);

					if (!$param['name']) {
						continue;
					}

					$name = $param['name'];
					unset($param['name']);

					$params[$name] = $param;
					continue;
				}

				if ($tag[0] == 'return') {

					$return = $this->parseReturnLin($line);
					continue;
				}


//				$tags[$tag[0]] = $tag[1];
				$tags[] = [
					'tag' => $tag[0],
					'value' => @$tag[1],
				];
				continue;
			}

			$line = preg_replace('/<br\s*\/?>$/i', '', $line);

			$description[] = $line;
		}

		if ($description) {
			$result['description'] = $description;
		}

		$result['example'] = $class;

		if ($method) {
			$result['example'] .= '::' . $method;
		}


		if ($params) {
//			debug($params);
			$result['param'] = $params;
		}

		if ($return) {
			$result['return'] = $return;
		}

		if ($tags) {
			$result['tags'] = $tags;
		}
	

		return $result;
	}	

	
	
}
