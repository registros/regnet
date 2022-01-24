<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client\Event;

use Exception;
use Regnet\Client\ClassMapper;
use Regnet\Client\Request;

/**
 * Called on cliente request
 *
 * @author Adrian Zurkiewicz
 */
interface OnRequest {

	/**
	 * 
	 * @param Request $request
	 * @param ClassMapper $class
	 * @throws Exception
	 */
	public function onRPC(Request $request, ClassMapper $class);
	
	
	/**
	 * 
	 * @param Request $request
	 * @throws Exception
	 */	
	public function onRequest(Request $request);
	
}
