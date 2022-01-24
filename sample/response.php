<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

//require 'path_to_project/vendor/autoload.php';

use Regnet\Server\Response;

try {

	$response = new Response();
	$response->setResult('Hola mundo');
} catch (Exception $ex) {

	$response->setException($exc);
}

$response->dump();

