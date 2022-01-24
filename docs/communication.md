# Communication standards  #

The data between the nodes is being transmitted through the standardized array, and using the JSON standard for serialization.

## Input ##
```php
$input = [
	'id' => '',	// Custom value. The same value will be returned as response of server.
	'timestamp' => time(),
	'data' => $data,
	'hashing' => '', // Optional, e.g. sha256
	'signature' => '', // Optional, hash(..., json_encode($input) . $key)
];
```
  
```php
// Example of RPC input format:
$input = [
	'rpc' => '1.0', 
	'id' => '',	// Custom value. The same value will be returned as response of server.
	'timestamp' => 1637784951,
	'data' => [
		'class' => 'Test',
		'constructor' => ['foo', 'bar'],
		'method' => 'get',
		'arguments' => ['baz'],
	],
	'hashing' => 'sha256',
	'signature' => 'b886e0019b20b53c8ace83526b6c976a744bf15996afa0646b67209f3f7a9273',
];
```
## output ##
```php
// Successful result
$output = [
	'id' => '',
	'timestamp' => time(),
	'success' => TRUE,
	'result' => $result,
];

// Failed or exception
$output = [
	'id' => '',
	'timestamp' => time(),
	'success' => FALSE,
	'error' => [
		'code' => $code, // Integer
		'message' => $message, // String
	],
];
```
