<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

//require 'path_to_project/vendor/autoload.php';

use Regnet\Server\Server;

$server = new Server();
$server->getExplorer()->setAttribute('namespace', 'Regnet\\Sample'); // Optional. If not set, the default namespace will be created.
$server->dump();
