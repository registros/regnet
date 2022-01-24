<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client;

/**
 * Client request
 *
 * @author Adrian Zurkiewicz
 */
class Request {
	
	/**
	 *
	 * @var string
	 */
	private $url;
	
	/**
	 *
	 * @var mixed 
	 */
	private $data;
	
	/**
	 *
	 * @var string
	 */
	private $key;
	
	
	/**
	 *
	 * @var array
	 */
	private $attributes = [];
	
	/**
	 *
	 * @var array 
	 */
	private $headers = [];
	
	/**
	 * Prepare request to server
	 * 
	 * @param string $url
	 * @param mixed $data
	 * @param ?string $key API key to for signature
	 */	
	function __construct(string $url, $data = null, string $key = null) {
		$this->url = $url;
		$this->data = $data;
		$this->key = $key;
		$this->setHeader(ACTION_HEADER, ACTION_REQUEST);
	}

		/**
	 * 
	 * @param mixed $data
	 * @return void
	 */
	function setData($data): void {
		$this->data = $data;
	}

	/**
	 * API key to for signature
	 * 
	 * @param string $key
	 * @return void
	 */
	function setKey(string $key): void {
		$this->key = $key;
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	function setAttribute(string $name, $value): void {
		$this->attributes[$name] = $value;
	}

	/**
	 * 
	 * @return array
	 */
	public function getRequest(): array {
		
		$result = $this->attributes;
		
		if (!isset($result['id'])) {
			$result['id'] = uniqid(); 
		}
		
		$result['timestamp'] = time();
		$result['data'] = $this->data;
		
		if ($this->key) {

			$result['hashing'] = 'sha256';
			$signature = json_encode($result) . $this->key;
			$signature = hash($result['hashing'], $signature);
			$result['signature'] = $signature;
		}

//		print_r($result);	
		
		return $result;
		
	}

	/**
	 * 
	 * @return string
	 */
	function getUrl(): string {
		return $this->url;
	}

	/**
	 * 
	 * @return array
	 */
	function getHeaders(): array {
		return $this->headers;
	}

	/**
	 * 
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	function setHeader(string $name, string $value): void {
		
		if ($value === '') {
			
			unset($this->headers[$name]);
			return;
			
		}
		
		$this->headers[$name] = $value;
	}

	
	
}
