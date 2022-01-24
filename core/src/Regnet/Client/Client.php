<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client;

use Exception;
use Regnet\Client\Event\OnRequest;
use Regnet\Client\Event\OnResponse;
use Regnet\Helper\Event;
use Throwable;
use const CONTEXT_CLIENT;

/**
 * Manages and secures server calls.
 *
 * @author Adrian Zurkiewicz
 */
class Client {

	/**
	 *
	 * @var bool
	 */
	private $exceptions = true;

	/**
	 *
	 * @var string
	 */
	private $error = null;

	/**
	 * 
	 * @param bool $exceptions Allow throw exceptions
	 */
	function __construct(bool $exceptions = true) {
		$this->exceptions = $exceptions;
	}

	/**
	 * 
	 * @param Throwable $exc
	 * @throws Throwable
	 */
	private function throw(Throwable $exc) {

		$this->error = $exc->getMessage();

		if ($this->exceptions) {

			throw $exc;
		}
	}

	/**
	 * 
	 * @return string
	 */
	private function getFields(array $post) {

		$result = [];

		foreach ($post as $key => $value) {

			$result[] = "{$key}=" . urlencode($value);
		}

		return implode('&', $result);
	}

	/**
	 * 
	 * @param string $url
	 * @param string|array|null $post Array to send POST fields, or raw string
	 * @param array $headers Additional headers as associative array. Each pair of key value will be written as one header.
	 * @return string
	 */
	
	/**
	 * 
	 * @param string $url
	 * @param string|array|null $post Array to send POST fields, or raw string
	 * @param array $headers HTTP headers
	 * @return string
	 * @throws Exception
	 * @throws RedirectException
	 */
	public function curl(string $url, $post = null, array $headers = []): string {

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		/*
		 * Headers
		 */
		if ($headers) {
			
			$curl_headers = [];

			foreach ($headers as $key => $value) {
				
				$curl_headers[] = "{$key}: {$value}";				
			}

			curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_headers);
		}

		/*
		 * Post
		 */
		if ($post) {

			if (is_array($post)) {

				$post = $this->getFields($post);
			}

			$post = (string) $post;

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		}

		
		/*
		 * Result
		 */
		$result = trim(curl_exec($curl));

		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($httpcode != 200) {
			
			if ($httpcode == 307) {
				
				$url = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
				$exception = new RedirectException('This is not an URL of explorer. Go to ' . $url);
				$exception->setUrl($url);
				$this->throw($exception);
			
			}
			
			$this->throw(new Exception("Unexpected server response (http code: {$httpcode})"));
		}


		return $result;
	}

	/**
	 * Run server query
	 * 
	 * @param string $url
	 * @param string|array|null $post Array to send POST fields, or raw string
	 * @param array $headers HTTP headers
	 * @return mixed
	 * @throws Exception
	 */
	public function query(string $url, $post = null, array $headers = []) {

		$this->error = null;

		$data = $this->curl($url, $post, $headers);


		if (!$data) {

			$this->throw(new Exception("Unexpected return format: empty string"));
			return null;
		}

		$data = json_decode($data, true);

		if (!is_array($data)) {

			$this->throw(new Exception("Unexpected return format: it is not an array"));
			return null;
		}

		if (!@$data['success']) {

			$error = 'Unknown remote error';
			$code = 0;

			if (@$data['error']['message']) {

				$error = $data['error']['message'];
				$code = (int) @$data['error']['code'];
			}

			$this->throw(new ServerException($error, $code));
			return null;
		}

		if (!array_key_exists('result', $data)) {

			$this->throw(new Exception("Unexpected return format: no result field"));
			return null;
		}


		$event = Event::create(CONTEXT_CLIENT, 'onResponse', [OnResponse::class]);

		if ($event) {

			$event->onResponse($data);
		}

//		debug($data);
		return $data['result'];
	}

	/**
	 * 
	 * @return string|null
	 */
	function getError(): ?string {
		return $this->error;
	}

	/**
	 * Send request
	 * 
	 * @param Request $request
	 * @return mixed
	 */
	public function request(Request $request) {

		$event = Event::create(CONTEXT_CLIENT, 'onRequest', [OnRequest::class]);

		if ($event) {

			$event->onRequest($request);
		}

		$url = $request->getUrl();
		$input = $request->getRequest();
		
//		debug($input);
		return $this->query($url, json_encode($input), $request->getHeaders());
	}

}
