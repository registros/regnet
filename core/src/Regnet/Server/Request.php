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
use Regnet\Helper\Event;
use Regnet\Server\Event\OnRequest;
use const CONTEXT_SERVER;
use function hash;

/**
 * Manage and secure the request input.
 *
 * @author Adrian Zurkiewicz
 */
class Request {

	/**
	 * 
	 */
	const TTL = 60;

	/**
	 *
	 * @var array|null 
	 */
	private $input = null;

	/**
	 *
	 * @var string
	 */
	private $key;

	/**
	 *
	 * @var bool|array 
	 */
	private $my_network = false;

	/**
	 *
	 * @var bool|array 
	 */
	private $networks = false;

	/**
	 * 
	 * @param string $key API key: If configured, signature will be required. 
	 */
	function __construct(string $key = null) {
		$this->key = $key;
	}

	/**
	 * 
	 * @return array
	 * @throws Exception
	 */
	private function getMyNetwork(): array {

		$result = Etc::get('server');
		return is_array(@$result['mynetwork']) ? $result['mynetwork'] : [];
	}

	/**
	 * 
	 * @throws Exception
	 */
	private function read() {


		if ($this->networks) {

			if (!$this->isPermittedNetwork()) {
				throw new Exception("Access denied: You're not on permitted network");
			}
		}


		if ($this->my_network) {

			if (!$this->isMyNetwork()) {
				throw new Exception("Access denied: You're not on my network");
			}
		}

		$input = trim(file_get_contents("php://input"));

		if (!$input) {
			throw new Exception("Unexpected input format: empty string");
		}

		$input = json_decode($input, true);

		if (!is_array($input)) {
			throw new Exception("Unexpected input format: input is not an array");
		}

		if (!array_key_exists('timestamp', $input)) {
			throw new Exception("Unexpected input format: no 'timestamp' field");
		}

		if (!array_key_exists('data', $input)) {
			throw new Exception("Unexpected input format: no 'data' field");
		}

		$this->input = $input;


		$this->check();

		$event = Event::create(CONTEXT_SERVER, 'onRequest', [OnRequest::class]);

		if ($event) {

			try {

				$event->onRequest($this);
			} catch (Throwable $exc) {

				$this->input = null;
				throw $exc;
			}
		}
	}

	/**
	 * 
	 * @throws Exception
	 */
	private function check() {



		if (time() - (int) @$this->input['timestamp'] > self::TTL) {
			throw new Exception("Request is too old");
		}



		if ($this->key) {

			if (!array_key_exists('signature', $this->input)) {

				throw new Exception("Unexpected input format: no 'signature' field. Set API key on client side.");
			}

			if (!array_key_exists('hashing', $this->input)) {
				throw new Exception("Unexpected input format: no 'hashing' field");
			}

			$test = $this->input;
			unset($test['signature']);

			$test = json_encode($test) . $this->key;
			$test = hash($this->input['hashing'], $test);

			if ($test != $this->input['signature']) {
				throw new Exception("Access denied: Wrong signature");
			}
		}
	}

	/**
	 * 
	 * @return mixed
	 */
	public function getData() {

		if ($this->input === null) {
			$this->read();
		}

		return $this->input['data'];
	}

	/**
	 * 
	 * @return array
	 */
	public function getInput(): array {

		if ($this->input === null) {
			$this->read();
		}

		return $this->input;
	}

	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getAttribute(string $name) {

		if ($this->input === null) {
			$this->read();
		}

		return @$this->input[$name];
	}

	/**
	 * 
	 * @return mixed
	 */
	public function getId() {

		if ($this->input === null) {
			$this->read();
		}

		return @$this->input['id'];
	}

	/**
	 * Checks if the address is part of subnet.
	 * 
	 * @param string $ip IPv4 address
	 * @param string $range Subnet as CIDR or simple IPv4 address
	 * @return bool
	 */
	private function cidrMatch($ip, $range): bool {

		if (strpos($range, '/') === false) {

			$range .= '/32';
		}

		list ($subnet, $bits) = explode('/', $range);

		$ip = ip2long($ip);
		$subnet = ip2long($subnet);
		$mask = -1 << (32 - $bits);
		$subnet &= $mask;

		return ($ip & $mask) == $subnet;
	}

	/**
	 * 
	 * @return bool
	 */
	private function isPermittedNetwork(): bool {

		$cliente = $_SERVER["REMOTE_ADDR"];

		foreach ($this->networks as $network) {

			if ($this->cidrMatch($cliente, $network)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 
	 * @return bool
	 */
	private function isMyNetwork(): bool {

		$networks = $this->getMyNetwork();
		$cliente = $_SERVER["REMOTE_ADDR"];

		foreach ($networks as $network) {

			if ($this->cidrMatch($cliente, $network)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Allow access only from my network.
	 * 
	 * @param bool $value
	 * @return void
	 */
	function setOnlyMyNetwork(bool $value): void {

		if ($this->my_network != $value) {
			$this->input = null;
		}

		$this->my_network = $value;
	}

	/**
	 * Allow access only from determined networks.
	 * 
	 * @param array $networks
	 * @return void
	 */
	function setNetworks(array $networks): void {

		$this->input = null;
		$this->networks = $networks;
	}

	/**
	 * 
	 * @param string $header
	 * @return string|null
	 */
	public function getHeader(string $header): ?string {

		$header = strtolower($header);
		$headers = $this->getHeaders();

		foreach ($headers as $name => $value) {

			if ($header == strtolower($name)) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * 
	 * @return array
	 */
	public function getHeaders(): array {

		if (!function_exists('getallheaders')) {

			$result = [];
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$result[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			return $result;
		}

		return getallheaders();
	}

	/**
	 * 
	 * @return bool
	 */
	public function isPost(): bool {

		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

}
