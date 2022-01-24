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

/**
 * On found redirect exception
 *
 * @author Adrian Zurkiewicz
 */
class RedirectException extends Exception {
	
	/**
	 *
	 * @var string
	 */
	private $url;
	
	/**
	 * 
	 * @return string
	 */
	function getUrl(): string {
		return $this->url;
	}

	/**
	 * 
	 * @param string $url
	 * @return void
	 */
	function setUrl(string $url): void {
		$this->url = $url;
	}


	
	
}
