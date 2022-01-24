<?php

/**
 * Registros en la Red
 * Copyright (c) Registros en la Red
 *
 * @copyright   Registros en la Red
 * @link        http://registros.net
 */

namespace Regnet\Client;

use Exception;
use Regnet\Helper\Cli;
use Throwable;
use const ACTION_EXPLORE;
use const DEBUG;
use const REGNET_LOCAL_DIR;

/**
 * Mapping of remote API to local namespace
 *
 * @author Adrian Zurkiewicz
 */
class Mapper {

	/**
	 *
	 * @var array
	 */
	private $data;

	/**
	 *
	 * @var array 
	 */
	private $classes;

	/**
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Url to Regnet API endpoint
	 * 
	 * @var string 
	 */
	private $explorer;

	/**
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Base dir
	 *
	 * @var string 
	 */
	private $dir;

	/**
	 *
	 * @var string 
	 */
	private $id;

	/**
	 *
	 * @var string
	 */
	private $base_namespace;

	/**
	 *
	 * @var List of created classes 
	 */
	private $created = [];
	
	/**
	 * Read data from explorer URL.
	 * 
	 * @param string $url
	 * @return array
	 * @throws Exception
	 * @throws RedirectException
	 */
	static public function read(string $url): array {
		
		$client = new Client();
		$request = new Request($url);
		$request->setHeader(ACTION_HEADER, ACTION_EXPLORE);
		
		return $client->request($request);
		
//		return $client->curl($url, null, [ACTION_HEADER => ACTION_EXPLORE]);			
	}	
	
	/**
	 * 
	 * @param string $dir
	 */
	static public function rmDir(string $dir) {

		if (is_dir($dir)) {

			foreach (scandir($dir) as $entry) {

				if (in_array($entry, ['.', '..'])) {
					continue;
				}

				$entry = "{$dir}/{$entry}";

				if (is_dir($entry)) {

					self::rmDir($entry);
				} else {

					unlink($entry);
				}
			}

			rmdir($dir);
		}
	}

	/**
	 * 
	 * @return bool
	 */
	static public function upgradeAll(): bool {

		$result = false;
		$list = self::getList();

		if (!$list) {
			Cli::output(null);
			Cli::output('No packages have been installed.');
			Cli::output(null);
			return FALSE;
		}

		foreach ($list as $id => $packages) {

			try {

				self::upgrade($packages);
				$result = true;
			} catch (Throwable $exc) {
				Cli::output($exc);
			}
		}

		return $result;
	}

	/**
	 * 
	 * @param array $packages
	 * @return type
	 */
	static public function upgrade(array $packages) {

//		debug($packages);		

		Cli::output('Checking : ' . $packages['explorer']);

		$mapper = new self($packages['explorer']);

		if ($mapper->getHash() == $packages['hash']) {

//			Cli::output('Updated, nothing to do.'); // @FIXME
//			Cli::output(null);
//			return;
		}

		Cli::output('Will be updated');
		Cli::output('Found ' . $mapper->getClassesNumber() . ' class(es)');

		$mapper->setNamespace($packages['namespace']);
		$mapper->run();

		Cli::output(null);
	}

	/**
	 * 
	 * @param string $id
	 * @return array|null
	 */
	static public function getPackage(string $id): ?array {

		$list = self::getList();

		if (isset($list[$id])) {
			return $list[$id];
		}

		return null;
	}

	/**
	 * 
	 * @param string $explorer Url of explorer
	 * @return array|null
	 */
	static public function getPackageByExplorer(string $explorer): ?array {

		$explorer = trim($explorer);
		
		$list = self::getList();
	
		foreach ($list as $package) {

			if ($package['explorer'] == $explorer) {
				return $package;
			}
		}
		
		return null;
	}

	/**
	 * 
	 * @return array
	 */
	static public function getList(): array {

		$result = [];
		$dir = REGNET_LOCAL_DIR . '/local/map/';


		foreach (scandir($dir) as $entry) {

			if (in_array($entry, ['.', '..'])) {
				continue;
			}

			$id = $entry;
			$entry = "{$dir}{$entry}";

			if (!is_dir($entry)) {
				continue;
			}


			$meta_file = $entry . '/meta.db';

			if (!file_exists($meta_file)) {

				Cli::output("Lost meta.db file in {$entry}", Cli::COLOR_RED);
				Cli::output("-> You should delete this folder.", Cli::COLOR_RED);
				continue;
			}

			$meta = file_get_contents($meta_file);
			$meta = json_decode($meta, TRUE);

			if (!is_array($meta)) {

				Cli::output("Unexpected file format: {$meta_file}", Cli::COLOR_RED);
				Cli::output("-> Add the integration package again.", Cli::COLOR_RED);
				continue;
			}

			if ((!@$meta['hash']) or (!@$meta['namespace']) or (!@$meta['explorer'])) {

				Cli::output("Unexpected file format: {$meta_file}", Cli::COLOR_RED);
				Cli::output("-> Add the integration package again.", Cli::COLOR_RED);
				continue;
			}

			$result[$id] = [
				'id' => $id,
				'dir' => $entry,
				'hash' => $meta['hash'],
				'namespace' => $meta['namespace'],
				'explorer' => $meta['explorer'],
			];
		}





		return $result;
	}

	/**
	 * 
	 * @param string $explorer Url to Regnet API endpoint
	 */
	function __construct(string $explorer) {
		
		if ($package = self::getPackageByExplorer($explorer)) {

			Cli::output("A package of this URL already exists. Namespace: " . $package['namespace'], Cli::COLOR_RED);
			$continue = Cli::input('Do you want to continue? [y/n]', true);

			if ($continue != 'y') {

				Cli::output('Cancelado');
				Cli::output(null);
				exit;
			}
		}		

		$this->explorer = $explorer;
		$this->data = $this->getData($explorer);

		$this->hash = $this->data['hash'];
		$this->url = $this->data['url'];


		$this->classes = $this->getClesses($this->data['class']);

		$this->id = sha1($explorer);
		$this->dir = REGNET_LOCAL_DIR . '/local/map/' . $this->id;
	}
	
	/**
	 * 
	 * @param string $explorer
	 * @return array
	 * @throws Exception
	 */
	private function getData(string $explorer): array {
		
		$result = self::read($explorer);
//		$result = json_decode($result, true);
		
		if (@$result['error']['message']) {
			
			throw new Exception($result['error']['message']);
		}
		
		if (!is_array($result)) {
			throw new Exception('This is not a correct url for regnet explorer');
		}

		if (!@$result['url']) {
			throw new Exception('URL attribute for explorer has not been configured');
		}

		if (!@$result['hash']) {
			throw new Exception('Unknown data hash');
		}

		if (!is_array(@$result['class'])) {
			throw new Exception('There are no classes to import');
		}

//		debug($result);
		return $result;
	}

	/**
	 * 
	 * @return int
	 */
	public function getClassesNumber(): int {

		return count($this->classes);
	}

	/**
	 * 
	 */
	private function create() {


		self::rmDir($this->dir);

		foreach ($this->classes as $data) {

			$this->createClasse($data);
		}


		/*
		 * Meta
		 */
		$meta = [];
		$meta['hash'] = $this->hash;
		$meta['namespace'] = $this->getNamespace();
		$meta['url'] = $this->url;
		$meta['explorer'] = $this->explorer;
		$meta['classes'] = $this->created;

		if (@$this->data['explorer']) {
			$meta['explorer'] = $this->data['explorer'];
		}

		if (@$this->data['download']) {
			$meta['download'] = $this->data['download'];
		}

		$meta = json_encode($meta);
		$file = $this->dir . '/meta.db';
		$this->saveFile($file, $meta);
	}

	/**
	 * 
	 * @param array $data
	 * @return array
	 */
	private function getClesses(array $data): array {

		if ((@$data['methods']) and (@$data['name'])) {

			return [$data];
		}

		$result = [];

		foreach ($data as $element) {

			if (is_array($element)) {

				$sub = $this->getClesses($element);
				$result = array_merge($result, $sub);
			}
		}

		return $result;
	}

	/**
	 * 
	 * @param array $data
	 */
	private function createClasse(array $data) {

//		debug($data);

		$result = [];

		$name = $this->parseClassName($data['name']);
		$data['name'] = $name;
		
		if (!is_array(@$data['tags'])) {
			$data['tags'] = [];
		}
		
		$data['tags'][] = [
			'tag' => 'generated',
			'value' => 'class generated using RegNET tools, please DO NOT EDIT!',
		];

		$result[] = '<?php';
		$result[] = null;
		$result[] = "namespace {$name['namespace']};";
		$result[] = null;
		$result[] = 'use Regnet\Client\ClassMapper;';
		$result[] = null;
		$result[] = $this->getDocblock($name['class'], $data, 'class');
		$result[] = "class {$name['short']} extends ClassMapper { ";
		$result[] = null;
		$result[] = $this->createMethods($data['methods'], $data);
		$result[] = null;
		$result[] = "}";


		$result = implode("\n", $result);

//		$result = htmlentities($result);
//		debug($result);

		$this->created[] = $name['class'];
		$this->saveClass($name['name'], $result);
	}

	/**
	 * 
	 * @param array $data
	 * @param array $class
	 * @return string
	 */
	private function createMethods(array $data, array $class): string {

//		debug($data);
//		debug($class);

		$result = [];

		$constructor = @$data['__construct'];
		$constructor = $constructor ? $constructor : [];
		$result[] = $this->createMethod('__construct', $constructor, $class);

		foreach ($data as $name => $method) {

			if ($name == '__construct') {
				continue;
			}

			$result[] = $this->createMethod($name, $method, $class);
		}

//		debug($result);
		return implode("\n", $result);
	}

	/**
	 * 
	 * @param string $name
	 * @param array $data
	 * @param array $class
	 * @return string
	 */
	private function createMethod(string $name, array $data, array $class): string {

//		debug($data);
//		debug($class);

		$return = (string) @$data['return']['type'];

		if ($return) {

			$return = ($this->isDeclarativeType($return)) ? $return : null;
			$return = ($return == 'boolean') ? 'bool' : $return;
			$return = $return ? ": {$return}" : null;
		}


		$params = (is_array(@$data['param'])) ? $this->getParams($data['param']) : null;

		$result = [];
		$result[] = $this->getDocblock($name, $data, 'method');
		$result[] = "public function {$name}($params){$return} {";
		$result[] = null;

		$params = (is_array(@$data['param'])) ? $this->getParams($data['param'], false) : null;

		if ($name == '__construct') {

			$params = "'{$this->url}', '{$class['name']['class']}', " . $params;
			$params = str_replace('\\', '\\\\', $params);
			$params = trim($params, ', ');
			$result[] = "\tparent::__construct({$params});";
		} else {

			$result[] = "\treturn parent::{$name}({$params});";
		}

		$result[] = null;
		$result[] = "}";
		$result[] = null;

//		debug($result);

		return "\t" . implode("\n\t", $result);
	}

	/**
	 * 
	 * @param string $name
	 * @param array $data
	 * @param string $context
	 * @return string
	 */
	private function getDocblock(string $name, array $data, string $context): string {

//		debug($data);

		$result = [];

		$prefix = null;

		if ($context == 'method') {
			$prefix = "\t";
		}


		$description = @$data['description'];
		$description = $description ? $description : ["Description of {$name}"];

		foreach ($description as $i => & $line) {

			if ($i < count($description) - 1) {
				$line .= '<br>';
			}
		}

		$result = $description;

		if (@$data['access']) {
			$result[] = null;
			$result[] = 'Access: ' . $data['access'];
		}


		if ((@$data['example']) and ($context == 'method')) {
			$result[] = null;
			$result[] = '<code>';
			$result[] = $data['example'];
			$result[] = '</code>';
		}

		$result[] = null;


		if ($context == 'method') {

			if (is_array(@$data['param'])) {

				foreach ($data['param'] as $key => $value) {

					$result[] = trim("@param " . (@$value['type'] ? $value['type'] : 'type') . " \${$key} " . @$value['description']);
				}
			}

			if (is_array(@$data['return'])) {

				$type = (@$data['return']['type'] ? $data['return']['type'] : 'type');
				$type = ($type == 'boolean') ? 'bool' : $type;
				$result[] = trim("@return {$type} " . @$data['return']['description']);
			}
		}


		if (is_array(@$data['tags'])) {
			
			foreach ($data['tags'] as $tag) {
				
				$tag['value'] = @$tag['value'] === true ? null : $tag['value'];
				$tag['value'] = is_string($tag['value']) ? trim($tag['value']) : $tag['value'];

				$result[] = "@{$tag['tag']} {$tag['value']}";
			}
		}


		foreach ($result as & $line) {

			$line = "{$prefix} * " . $line;
		}

		$result = array_merge(["{$prefix}/**"], $result);
		$result = array_merge($result, ["{$prefix} */"]);

//		debug($result);

		return trim(implode("\n", $result));
	}

	/**
	 * 
	 * @param array $data
	 * @param bool $declaration
	 * @return string
	 */
	private function getParams(array $data, bool $declaration = true) {

		$result = [];

		foreach ($data as $name => $param) {

			$type = null;
			$default = null;

			if ($declaration) {
				$type = (string) @$param['type'];
				$type = ($this->isDeclarativeType($type)) ? $type : null;

				if (array_key_exists('default', $param)) {

					$default = " = " . $this->getDefault($param['default']);
				}
			}

			$result[] = trim($type . ' $' . $name . $default);
		}

		return implode(', ', $result);
	}

	/**
	 * 
	 * @param mixed $default
	 * @return string
	 */
	private function getDefault($default): string {

		if (is_numeric($default)) {
			return $default;
		}

		if (is_bool($default)) {
			return $default ? 'true' : 'false';
		}

		if (is_string($default)) {

			$default = str_replace("'", "\\'", $default);
			$default = "'{$default}'";
			return $default;
		}

		if (is_array($default)) {

			$result = [];

			foreach ($default as $key => $value) {

				$key = $this->getDefault($key);
				$value = $this->getDefault($value);

				$result[] = "{$key} => {$value}";
			}

			$result = implode(',', $result);

			return "[{$result}]";
		}

		return $this->getDefault((string) $default);
	}

	/**
	 * 
	 * @param string $type
	 * @return bool
	 */
	private function isDeclarativeType(string $type): bool {

		return in_array($type, ['string', 'int', 'integer', 'float', 'bool', 'boolean', 'array']);
	}

	/**
	 * 
	 * @return string
	 */
	function getNamespace(): string {

		if ($this->base_namespace) {
			return $this->base_namespace;
		}

		if (@$this->data['namespace']) {

			$this->base_namespace = $this->data['namespace'];
			return $this->base_namespace;
		}



		$base = $this->url;
		$base = explode('://', $base, 2);
		$base = end($base);
		$base = explode('/', $base, 2);
		$base = $base[0];
		$base = explode('#', $base, 2);
		$base = $base[0];
		$base = explode('?', $base, 2);
		$base = $base[0];
		$base = explode('.', $base);

		foreach ($base as & $part) {

			$part = strtolower($part);
			$part = ucfirst($part);
		}

		$this->base_namespace = implode('', $base);

		return $this->base_namespace;
	}

	/**
	 * 
	 * @param string $base_namespace
	 * @return void
	 */
	function setNamespace(string $namespace): void {

		$namespace = str_replace('\\\\', '\\', $namespace);
		$namespace = trim($namespace, '\\ ');

		$list = self::getList();

		foreach ($list as $id => $packages) {

			if (($packages['namespace'] == $namespace) and ($id != $this->id)) {

				Cli::Output("Warning! A package with name '{$namespace}' already exists and will be overwrite");
				break;
			}
		}

		$this->base_namespace = $namespace;
	}

	/**
	 * 
	 * @param string $name
	 * @return array
	 */
	private function parseClassName(string $name): array {

		$result = [];

		$result['class'] = $name;
		$result['name'] = $this->getNamespace() . '\\' . $name;
		$namespace = explode('\\', $result['name']);

		$result['short'] = array_pop($namespace);
		$result['namespace'] = implode('\\', $namespace);


		return $result;
	}

	/**
	 * 
	 * @param string $name
	 * @param string $contents
	 */
	private function saveClass(string $name, string & $contents) {

		$name = str_replace('\\', '/', $name);
		$file = $this->dir . '/' . $name . '.php';
		$this->saveFile($file, $contents);
	}

	/**
	 * 
	 * @param string $file
	 * @param string $contents
	 * @throws Exception
	 */
	private function saveFile(string $file, string & $contents) {

		$dir = pathinfo($file, PATHINFO_DIRNAME);

		if (!is_dir($dir)) {

			mkdir($dir, 0777, true);

			if (!is_dir($dir)) {
				throw new Exception('Directory cannot be created: ' . $dir);
			}
		}

		if (DEBUG) {
			Cli::output('Create file: ' . $file);
		}

		file_put_contents($file, $contents);
	}

	/**
	 * 
	 * @return string
	 */
	function getHash(): string {
		return $this->hash;
	}

	/**
	 * 
	 */
	public function run() {

		$this->create();
	}

}
