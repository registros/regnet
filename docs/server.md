# Server #

The components of the server.

## Response ##

Format and normalize the server response.

```php
use Regnet\Server\Response;

try {

	$response = new Response();
	$response->setResult('Hola mundo');
    
} catch (Exception $exc) {
	
	$response->setException($exc);
}

$response->dump();
```
## Request ##

Manage and secure the request input.

```php
use Regnet\Server\Request;

$request = new Request('*******');
$request->setOnlyMyNetwork(TRUE);
$data = $request->getData();
```
## ClassAPI ##

Allows external calls (RPC) to local classes.

```php
use Regnet\Server\ClassAPI;

$api = new ClassAPI(new Request('*******'));
$api->dump();
```

## Server ##

Export the classes and methods through the Regnet API. The security policy and the list of exported classes and methods is managed in the files ./regnet/local/etc/server.php

```php
use Regnet\Server\Server;

$server = new Server();
$server->getExplorer()->setAttribute('namespace', 'Regnet\\Sample'); // Optional. If not set, the default namespace will be created.
$server->dump();
```


