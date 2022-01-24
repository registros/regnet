<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Server\Explorer;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Regnet\Server\Server;

/**
 * Exploring the API
 *
 * @author Adrian Zurkiewicz
 */
class Explorer {

	/**
	 * 
	 * @var Server
	 */
	private $server;

	/**
	 *
	 * @var Docblock
	 */
	private $docblock;

	/**
	 *
	 * @var array 
	 */
	private $attributes = [];

	/**
	 * 
	 * @param Server $server
	 */
	function __construct(Server $server) {
		$this->server = $server;
		$this->docblock = new Docblock();

		$this->setAttribute('version', $server::VERSION);
		$this->setAttribute('hash', null);

		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

		$this->setAttribute('namespace', $this->getDefaultNamespace($url));
		$this->setAttribute('url', $url);
		$this->setAttribute('documenter', 'https://regnet.registros.net/documenter?url=' . rawurlencode($url));
	}

	/**
	 * 
	 * @param string $url
	 * @return string
	 */
	private function getDefaultNamespace(string $url): string {

		$result = explode('://', $url, 2);
		$result = end($result);

		$result = explode('/', $result, 2);
		$result = $result[0];

		$result = explode('?', $result, 2);
		$result = $result[0];

		$result = explode('.', $result);

		foreach ($result as & $part) {

			$part = ucfirst($part);
		}

		$result = array_reverse($result);

		return implode('\\', $result);
	}

	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttribute(string $key, $value) {

		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	 * 
	 * @param array $array
	 */
	private function searchExample(array & $array) {

		foreach ($array as $key => & $element) {

			if (is_array($element)) {

				$this->searchExample($element);
				continue;
			}

			if ($key === 'example') {

				$element = $this->createExample($array, $element);
				continue;
			}
		}
	}

	/**
	 * 
	 * @param array $array
	 * @param string $context
	 * @return string
	 */
	private function createExample(array & $array, string $context): string {

		$context = explode('::', $context);
		$class = $context[0];
		$method = @$context[1];

		if (!$method) { // Class
			return $this->createExampleClass($array, $class);
		}

		return $this->createExampleMethod($array, $class, $method);
	}

	/**
	 * 
	 * @param mixed $params
	 * @return string|null
	 */
	private function getParamsString($params): ?string {

		if (!$params) {
			return null;
		}

		if (is_array($params)) {

			$result = array_keys($params);

			foreach ($result as & $param) {

				$param = "\${$param}";
			}

			$result = implode(', ', $result);
		} else {

			$result = null;
		}

		return $result;
	}

	/**
	 * 
	 * @param array $array
	 * @param string $class
	 * @return string
	 */
	private function createExampleClass(array & $array, string $class): string {

		$constructor = $this->getParamsString(@$array['methods']['__construct']['param']);
		$constructor = $constructor ? ', ' . $constructor : null;

		$short_class = explode('\\', $class);
		$short_class = end($short_class);

		$url = @$this->attributes['url'] ? $this->attributes['url'] : 'https://server...';

		return "\${$short_class} = new ClassMapper('{$url}', '{$class}'{$constructor});";
	}

	/**
	 * 
	 * @param array $array
	 * @param string $class
	 * @param string $method
	 * @return string
	 */
	private function createExampleMethod(array & $array, string $class, string $method): string {

		$params = $this->getParamsString(@$array['param']);

		$short_class = explode('\\', $class);
		$short_class = end($short_class);

		$return = @$array['return']['type'];
		$return = trim(strtolower($return));

		if (!in_array($return, ['void', 'null', 'none', 'this', 'self', ''])) {

			$return = "\$result = ";
		} else {

			$return = null;
		}

		return "{$return}\${$short_class}->{$method}({$params});";
	}

	/**
	 * 
	 * @param string $class
	 * @param array $options
	 * @param array $result
	 */
	private function addClass(string $class, array $options, array & $result) {

		$info = $this->explorerClass($class, $options);
		$info = array_merge(['name' => $class], $info);

		$pointer = & $result;
		$path = explode('\\', $class);

		foreach ($path as $space) {

			if (!isset($pointer[$space])) {

				$pointer[$space] = [];
			}

			$pointer = & $pointer[$space];
		}

		$pointer = $info;
	}

	/**
	 * 
	 * @param array $options
	 * @return array
	 */
	private function getAccessInformation(array & $options): array {

		$info = [];
		$info[] = @$options['access'] ? 'Yes' : 'No';

		if (@$options['access']) {

			if (@$options['mynetwork']) {
				$info[] = 'Only my network';
			}

			if (@$options['key']) {
				$info[] = 'API key required';
			}

			if (is_array(@$options['network'])) {

				if ($options['network']) {
					$info[] = 'Networks: ' . implode(', ', $options['network']);
				}
			}
		}

		return ['access' => implode('; ', $info)];
	}

	/**
	 * 
	 * @param array $tags
	 * @param string $name
	 * @return array|null
	 */
	private function getTag(array $tags, string $name): ?array {

		foreach ($tags as $result) {

			if ($result['tag'] == $name) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * 
	 * @param string $class
	 * @param array $options
	 * @return array
	 */
	private function explorerClass(string $class, array $options): array {

		$rc = new ReflectionClass($class);
		$file = $rc->getFileName();

		$docblock = $rc->getDocComment();
		$result = $this->docblock->parse($docblock, $class);

		$deprecated = $this->getTag($result['tags'], 'deprecated');

//		if ($deprecated) {
//			debug($result['tags']);
//		}


		$deprecated = $deprecated ? @$deprecated['value'] : false;

		if ($deprecated !== false) {
			$deprecated = $deprecated ? $deprecated : true;
		}

		$result['tags'][] = [
			'tag' => 'version',
			'value' => date('r', filemtime($file)),
		];

		$result['methods'] = [];




		$access = $this->getAccessInformation($options);
		$result = array_merge($access, $result);

		$methods = $rc->getMethods();

		foreach ($methods as $method) {

			if ($method instanceof ReflectionMethod) {

				if (!$method->isPublic()) {
					continue;
				}

				$name = $method->getName();
				$method_options = $this->server->getMethodOptions($class, $name);

				if (!$method_options) {
					continue;
				}

				if ($deprecated) {
					$method_options['deprecated'] = $deprecated;
				}

				$result['methods'][$name] = $this->explorerMethod($method, $method_options);
			}
		}

		if (!$result['methods']) {
			unset($result['methods']);
		}

		return $result;
	}

	/**
	 * 
	 * @param ReflectionMethod $method
	 * @param array $options
	 * @return array
	 */
	private function explorerMethod(ReflectionMethod $method, array $options): array {

//		debug($options);

		$name = $method->getName();
		$docblock = $method->getDocComment();
		$result = $this->docblock->parse($docblock, $method->getDeclaringClass()->getName(), $name);

		if (!@$result['param']) {
			$result['param'] = [];
		}

		if (@$options['deprecated']) {

			if (!is_array(@$result['tags'])) {
				$result['tags'] = [];
			}

			if (!$this->getTag($result['tags'], 'deprecated')) {

				$deprecated = $options['deprecated'];
				$deprecated = is_string($deprecated) ? $deprecated : 'This method has been marked as deprecated, which means that in future versions it may disappear.';

				$result['tags'][] = [
					'tag' => 'deprecated',
					'value' => $deprecated,
				];
			}
		}


		if ($name == '__construct') {
			unset($result['example']);
		} else {
			$access = $this->getAccessInformation($options);
			$result = array_merge($access, $result);
		}


		$param_docs = @$result['param'];
		$param_docs = $param_docs ? $param_docs : [];



		$parameters = $method->getParameters();

		foreach ($parameters as $parameter) {

			$name = $parameter->getName();
			$doc = @$param_docs[$name];
			$doc = $doc ? $doc : [];

			$result['param'][$name] = $this->explorerParameter($parameter, $options, $doc);
		}

		if (!@$result['return']['type']) {

			$return_type = (string) $method->getReturnType();

			if ($return_type) {
				$result['return']['type'] = $return_type;
			}
		}

		if (!$result['param']) {
			unset($result['param']);
		}

		return $result;
	}

	/**
	 * 
	 * @param ReflectionParameter $parameter
	 * @param array $options
	 * @param array $doc
	 * @return array
	 */
	private function explorerParameter(ReflectionParameter $parameter, array $options, array $doc): array {

//		debug($doc);

		$type = $parameter->getType();
		$type = (string) $type;
		$type = $type ? $type : @$doc['type'];

//		$type = $doc['type'];

		$result = [];
		$result['name'] = $parameter->getName();

		if ($type) {
			$result['type'] = $type;
		}

		if (@$doc['description']) {
			$result['description'] = $doc['description'];
		}

		if ($parameter->allowsNull()) {
			$result['allows_null'] = true;
		}

		if ($parameter->isDefaultValueAvailable()) {

			$result['default'] = $parameter->getDefaultValue();
		}

		return $result;
	}

	/**
	 * 
	 * @return array
	 */
	public function getData(): array {


		$result = $this->attributes;
		$result['class'] = [];

		$classes = $this->server->getClasses();

		foreach ($classes as $class => $options) {

			$this->addClass($class, $options, $result['class']);
		}

		$this->searchExample($result);

		$hash = sha1(json_encode($result));
		$result['hash'] = $hash;


		return $result;
	}

}
