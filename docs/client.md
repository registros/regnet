# Client #

The components of the client.

## Client ##

Manages and secures server calls.

```php
use Regnet\Client\Client;
use Regnet\Client\Request;

$client = new Client();
$result = $client->query('https://server...'); // Waiting for standard output
$result = $client->request(new Request('https://server...', ['a' => 1, 'b' => 2], '*******')); // Call for Server -> Request class
```
## ClassMapper ##

Handler of remote method call (RPC).

```php
use Regnet\Client\ClassMapper;

$class_mapper = new ClassMapper('https://server...', 'Test', 'foo', 'bar');
$class_mapper->rpc->setKey('*******');
$result = $class_mapper->get('baz');

/*
 * // It is equivalent to running on the server:
 * $test = new Test('foo', 'bar');
 * $result = $test->get('baz');
 */

```
