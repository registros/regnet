<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Server\Event;

use Exception;

/**
 * Called on server response
 *
 * @author Adrian Zurkiewicz
 */
interface OnResponse {

	/**
	 * 
	 * @param array $output
	 * @throws Exception
	 */
	public function onResponse(array & $output);	
	
}
