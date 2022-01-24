# API sever #

The api server allows the export of classes and methods for remote access through a variety of RCP-JSON. In addition, the server can publish a standardized declaration that describes the classes and methods exported by the API server. This information is presented in JSON format. Using this declaration the API clients can import the classes that facilitate remote access.

## Installation ##

```bash
git clone https://github.com/registros/regnet.git
```

### Initialization ###

Run the Regnet manager to initialize the Regnet environment.

```bash
cd regnet
./regnet.php
```

### Configuration of API server ###

Edit the ./regnet/local/etc/server.php file to configure your security policy and to decide which classes and methods are exported through api.

### Integration ###

To integrate regnet into your project, include the init.php file at the beginning of the main entry.

```php
require '/path-to-source/regnet/core/init.php';
```

### API endpoint ###

```php
use Regnet\Server\Server;

$server = new Server();
$server->getExplorer()->setAttribute('namespace', 'Regnet\\Sample'); // Optional. If not set, the default namespace will be created.
$server->dump();
```
