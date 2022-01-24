<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */


$reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
$regnet_local_dir = dirname(dirname($reflection->getFileName()));
$regnet_local_dir .= '/../regnet/'; 

if (!is_dir($regnet_local_dir)) {
	
	mkdir($regnet_local_dir);
}


define('REGNET_LOCAL_DIR', realpath($regnet_local_dir));
define('REGNET_DIR', realpath(__DIR__ . '/../'));

define('CONTEXT_SERVER', 'server');
define('CONTEXT_CLIENT', 'client');
define('ACTION_REQUEST', 'request');
define('ACTION_EXPLORE', 'explore');
define('ACTION_RPC', 'rpc');
define('ACTION_HEADER', 'X-Regnet-Action');

include __DIR__ . '/loader.php';