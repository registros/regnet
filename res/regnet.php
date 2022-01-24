#!/bin/php
<?php
/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */
require __DIR__ . '/../vendor/autoload.php';

use Regnet\Client\Mapper;
use Regnet\Client\RedirectException;
use Regnet\Helper\Cli;

if (!defined('DEBUG')) {
	$shortopts = 'd'; // Debug
	$options = getopt($shortopts);
	define('DEBUG', isset($options['d']));
}

/**
 * 
 * @return bool
 */
function listPackages(): bool {

	$list = Mapper::getList();

	if (!$list) {

		Cli::output(null);
		Cli::output('No packages have been installed.');
		Cli::output(null);
		return false;
	}

	Cli::output(null);
	Cli::output('Installed integration packages:');
	Cli::output(null);


	foreach ($list as $id => $packages) {

		Cli::output("{$id} : {$packages['namespace']} ({$packages['explorer']})");
	}

	Cli::output(null);
	return true;
}

try {

	Cli::output(null);
	Cli::output('Regnet Manager');
	Cli::output('Powered by Registros en La Red, S.L.');
	Cli::output('https://registros.net');

	/*
	 * Init
	 */
	$init = null;

	if (!is_dir(__DIR__ . '/etc')) {

		Cli::output(null);
		Cli::output('Initializing the Regnet environment...');

		mkdir(__DIR__ . '/etc', 0777, true);
		$init = true;
	}

	if (!is_dir(__DIR__ . '/map')) {
		mkdir(__DIR__ . '/map', 0777, true);
	}

	if (!file_exists(__DIR__ . '/etc/server.php')) {
		$init = ($init and (copy(REGNET_DIR . '/res/etc/server.php', __DIR__ . '/etc/server.php')));
	}

	if (!file_exists(__DIR__ . '/etc/client.php')) {
		$init = ($init and (copy(REGNET_DIR . '/res/etc/client.php', __DIR__ . '/etc/client.php')));
	}
	
	if ($init !== null) {

		if ($init) {

			Cli::output('Regnet environment is ready!', Cli::COLOR_GREEN);
			Cli::output(null);
			Cli::output('Configuration files:');
			Cli::output(__DIR__ . '/etc/server.php');
			Cli::output(__DIR__ . '/etc/client.php');
			Cli::output(null);
			Cli::output("Run '\$ php regnet/regnet.php' to manage external API packages");
			Cli::output(null);
			exit;
			
		} else {

			throw new Exception("The Regnet environment could not be initialized");
		}
	}


	/*
	 * Management
	 */
	$action = Cli::choice('Choose an action to do', [
				'l' => 'List current integration packages',
				'u' => 'Upgrade all integration packages',
				'a' => 'Add new integration package (I know url for Regnet explorer)',
				'r' => 'Remove an integration package',
				'q' => 'Quit',
					], 'l');


	if ($action == 'l') {

		listPackages();
	}

	/*
	 * Upgrade
	 */
	if ($action == 'u') {

		if (Mapper::upgradeAll()) {

			Cli::output('Ready!', Cli::COLOR_GREEN);
			Cli::output(null);
		}
	}


	/*
	 * Add
	 */
	if ($action == 'a') {

		$url = Cli::input('Url to Regnet API endpoint');

		try {

			$mapper = new Mapper($url);
		} catch (RedirectException $exc) {

			$url = $exc->getUrl();

			Cli::output("Correct URL is: " . $url, Cli::COLOR_YELLOW);
			Cli::output('The url has been modified', Cli::COLOR_YELLOW);
			Cli::output(null);
			$mapper = new Mapper($url);
		}



		Cli::output('Found ' . $mapper->getClassesNumber() . ' class(es)');

		$namespace = $mapper->getNamespace();
		$namespace = Cli::input('Choice namespace', true, $namespace);

		$mapper->setNamespace($namespace);
		$mapper->run();
		Cli::output('Ready!', Cli::COLOR_GREEN);
		Cli::output(null);
	}

	/*
	 * Remove
	 */
	if ($action == 'r') {

		if (listPackages()) {

			$id = Cli::input('Enter package ID that will be removed', true);
			$package = Mapper::getPackage($id);

			if (!$package) {

				Cli::output('Package not found!', Cli::COLOR_RED);
				Cli::output(null);
				exit;
			}

			Mapper::rmDir($package['dir']);
			Cli::output('Ready!', Cli::COLOR_GREEN);
			Cli::output(null);
		}
	}
} catch (Throwable $exc) {
	Cli::output($exc);
}


