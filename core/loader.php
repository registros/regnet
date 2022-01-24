<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */
$src = [];

if (is_dir(REGNET_LOCAL_DIR . '/map/')) {

	foreach (scandir(REGNET_LOCAL_DIR . '/map/') as $entry) {

		if (in_array($entry, ['.', '..'])) {
			continue;
		}

		$src[] = REGNET_LOCAL_DIR . '/map/' . $entry;
	}
}

spl_autoload_register(function ($class) use ($src) {

	foreach ($src as $dir) {

		$file = str_replace('\\', '/', $class) . '.php';
		$file = $dir . '/' . $file;

		if (file_exists($file)) {

			include $file;
			return;
		}
	}
});


