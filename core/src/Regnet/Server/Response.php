<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Server;

use Regnet\Helper\Event;
use Regnet\Server\Event\OnResponse;
use Throwable;
use const CONTEXT_SERVER;
use const DEBUG;

/**
 * Format and normalize the server response.
 *
 * @author Adrian Zurkiewicz
 */
class Response {

	/**
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 *
	 * @var float
	 */
	private $start;

	/**
	 * 
	 * @param Request $request
	 */
	function __construct(Request $request = null) {

		$this->start = microtime(true);

		if (defined('DEBUG')) {
			$this->debug = DEBUG;
		}

		if ($this->debug) {
			$this->data['host'] = gethostname();
			$this->data['client'] = $_SERVER["REMOTE_ADDR"];
			$this->data['time'] = 0;
		}


		try {

			if (!$request) {
				$request = new Request();
			}

			$this->data['id'] = $request->getId();
		} catch (Throwable $exc) {
			$this->data['id'] = NULL;
		}

		$this->data['timestamp'] = time();
		$this->data['success'] = false;
	}

	/**
	 * 
	 * @param mixed $result
	 */
	public function setResult($result) {

		unset($this->data['error']);

		$this->data['success'] = true;
		$this->data['result'] = $result;
	}

	/**
	 * 
	 * @param string $message
	 * @param int $code
	 */
	public function setError(string $message, int $code = 0) {

		unset($this->data['error']);
		unset($this->data['result']);

		$this->data['success'] = false;

		$this->data['error'] = [
			'code' => $code,
			'message' => $message,
		];
	}

	/**
	 * 
	 * @param Throwable $exc
	 */
	public function setException(Throwable $exc) {

		$this->setError($exc->getMessage(), $exc->getCode());

		$this->data['error']['class'] = get_class($exc);

		if ($this->debug) {
			$this->data['error']['details'] = explode("\n", $exc);
		}
	}

	/**
	 * 
	 * @return bool
	 */
	public function isReady(): bool {

		return (array_key_exists('result', $this->data) or array_key_exists('error', $this->data));
	}

	
	
	/**
	 * Set or overwrite part of result information.
	 * 
	 * @param string $key
	 * @param type $value
	 */
	public function setData(string $key, $value) {
		
		$this->data[$key] = $value;
		
	}
	
	/**
	 * 
	 * @return array
	 */
	function getData(): array {

		if (!$this->isReady()) {
			$this->setError('The result has not been assigned');
		}

		if ($this->debug) {
			$this->data['time'] = round((microtime(true) - $this->start) * 1000);
		}

		$event = Event::create(CONTEXT_SERVER, 'onResponse', [OnResponse::class]);

		if ($event) {

			$event->onResponse($this->data);
		}

		if ($this->data['id'] === null) {

			unset($this->data['id']);
		}

		return $this->data;
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
			include __DIR__ . '/../../../html/500.phtml';
		}

		exit;
	}

}
