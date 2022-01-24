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
use Regnet\Helper\Etc;
use Regnet\Server\Explorer\Explorer;
use Throwable;
use const ACTION_RPC;

/**
 * API server
 *
 * @author Adrian Zurkiewicz
 */
class Server {

	/**
	 * 
	 */
	const VERSION = 0.1;

	/**
	 *
	 * @var Response
	 */
	private $response;

	/**
	 *
	 * @var Explorer
	 */
	private $explorer;
	
	/**
	 *
	 * @var bool 
	 */
	private $active_explorer = true;

	/**
	 *
	 * @var string
	 */
	private $action;
	
	/**
	 * 
	 * @return array
	 */
	private function getExport(): array {

		$result = Etc::get('server');
		return is_array(@$result['export']) ? $result['export'] : [];
	}

	/**
	 * Normalize options.
	 * 
	 * @param mixed $options
	 */
	private function options(& $options) {

		if (($options === true) or ($options === null)) {

			$options = [];
		}

		if (!is_array($options)) {
			$options = false;
			return;
		}

		$this->setDefaultValue($options, 'access', true);
		$this->setDefaultValue($options, 'mynetwork', true);
		$this->setDefaultValue($options, 'network', []);
	}

	/**
	 * 
	 * @param array $array
	 * @param string $key
	 * @param type $value
	 */
	private function setDefaultValue(array & $array, string $key, $value) {

		if (!array_key_exists($key, $array)) {
			$array[$key] = $value;
		}
	}

	/**
	 * 
	 * @param string $class
	 * @param string $method
	 * @return array|false
	 */
	public function getMethodOptions(string $class, string $method) {

		$options = $this->getExport();

		if (!array_key_exists($class, $options)) {
			return false;
		}

		$result = $options[$class];

		$this->options($result);

		if (is_array(@$result['methods'])) {

			if (array_key_exists($method, $result['methods'])) {

				$method_options = $result['methods'][$method];

				if ($method_options === false) {
					return false;
				}


				if (is_array($method_options)) {

					foreach ($method_options as $key => $value) {

						$result[$key] = $value;
					}
				}
			}
		}

		unset($result['methods']);

		return $result;
	}

	/**
	 * 
	 * @return array
	 */
	public function getClasses(): array {

		$result = [];

		foreach ($this->getExport() as $class => $options) {

			$this->options($options);

			if ($options === false) {
				continue;
			}

			$result[$class] = $options;
		}

		return $result;
	}

	/**
	 * 
	 * @param Response $response
	 * @param Request $request
	 */
	private function proc(Response $response, Request $request) {

		$data = $request->getData();
		$class = @$data['class'];
		$method = @$data['method'];

		if (!@$class['class']) {
			throw new Exception("Unexpected input format: no 'class' field");
		}

		if (!@$class['method']) {
			throw new Exception("Unexpected input format: no 'method' field");
		}

		$options = $this->getMethodOptions($class, $method);

		if ((!$options) or (!is_array($options))) {
			throw new Exception('You do not have permission to access this object');
		}

		if (!@$options['access']) {
			throw new Exception('You do not have permission to access this object');
		}

		if (@$options['key']) {

			$request = new Request($options['key']);
		}

		if (@$options['mynetwork']) {

			$request->setOnlyMyNetwork(true);
		}

		if (is_array(@$options['network'])) {

			$request->setNetworks($options['network']);
		}

		$api = new ClassAPI($request, $response);

		if (@$options['cache']) {

			$api->activeCache((int) $options['cache']);
		}

		$api->run();
	}

	
	/**
	 * 
	 * @return Explorer
	 */
	function getExplorer(): Explorer {
		
		if (!$this->explorer) {
			$this->explorer = new Explorer($this);
		}
		
		return $this->explorer;
	}


	/**
	 * 
	 * @param bool $active
	 * @return void
	 */
	function activeExplorer(bool $active): void {
		$this->active_explorer = $active;
	}

	
	
	/**
	 * 
	 * @return bool
	 */
	private function explorer(): bool {
		
		if (!$this->active_explorer) {
			return false;
		}
		
		$this->response->setResult($this->getExplorer()->getData());
		$this->action = ACTION_EXPLORE;
		
		return true;
	}

	/**
	 * 
	 */
	private function run() {

		try {

			$request = new Request();
			$this->response = new Response($request);

			if ((!$request->isPost()) or ($request->getHeader(ACTION_HEADER) == ACTION_EXPLORE)) {

				if ($this->explorer()) {
					return;
				}
				
			}

			$this->proc($this->response, $request);
			$this->action = ACTION_RPC;
			
		} catch (Throwable $exc) {
			$this->response->setException($exc);
		}
	}

	/**
	 * 
	 * @return array
	 */
	public function getData(): array {
		
		if (!$this->response) {
			$this->run();
		}

		return $this->response->getData();
	}

	/**
	 * 
	 */
	public function dump() {

		try {
						
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($this->getData());
			
		} catch (Throwable $error) {
			
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			header('Content-Type: text/html; charset=utf-8');
			include __DIR__ . '/../../html/500.phtml';
		}	
		
		exit;
		
	}

}
