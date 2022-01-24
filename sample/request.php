<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */
require '../core/init.php';

use Regnet\Server\Request;
use Regnet\Server\Response;

try {

	$response = new Response();

	$request = new Request();
	$data = $request->getData();

	$my_response = [
		'info' => 'It is a response from API server',
		'input_type' => gettype($data),
		'input_content' => $data,
	];

	$response->setResult($my_response);
} catch (Exception $exc) {

	$response->setException($exc);
}

$response->dump();
