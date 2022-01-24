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
use ReflectionClass;
use Regnet\Server\Request;

/**
 * Called on server request
 *
 * @author Adrian Zurkiewicz
 */
interface OnRequest {

	/**
	 * 
	 * @param Request $request
	 * @param ReflectionClass $reflection
	 * @throws Exception
	 */
	public function onRPC(Request $request, ReflectionClass $reflection);
	
	/**
	 * 
	 * @param Request $request
	 * @throws Exception
	 */
	public function onRequest(Request $request);	
	
}
