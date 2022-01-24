<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */
require __DIR__ . '/../../autoload.php';

use Regnet\Helper\Cli;

if (file_exists(REGNET_LOCAL_DIR . '/regnet.php')) {

	Cli::output(null);
	Cli::output('Regnet has already been installed.');
	Cli::output("Run '\$ php regnet/regnet.php' to manage external API packages");
	Cli::output(null);
	exit;
}

copy(__DIR__ . '/res/regnet.php', REGNET_LOCAL_DIR . '/regnet.php');
system(PHP_BINARY . ' ' . REGNET_LOCAL_DIR . '/regnet.php');
