# Regnet #

Regnet is a set of standards and tools to facilitate the communication within the distributed web infrastructure.  

[Home page](https://regnet.registros.net)

## Features ##

* Standardization of communication: client -> server -> client
* Authentication through API key
* Access control according to the client's IP address
* Central management of exported classes and methods (on the server side)
* Central security policy (on the server side)
* Automated mapping for the exported classes and methods (on the client side)
* Cache on both the server side and the client side  
* Object Oriented RPC engine  
* Online tool to explore and document API  

## Requirements ##

* PHP 7.2 (or later)
* Console access

## Installation ##

```bash
composer require registros/regnet
php vendor/registros/regnet/install.php
```

### API server ###

[To configure API server see the document docs/api-server.md](docs/api-server.md)

### Importing the integration packages ###

An integration package is a set of classes that maps the server api to facilitate use of api from the client. In order to import an integration package run the regnet manager.

To import an integration package you will need the url of the Regnet API endpoint. 

After running the manager use the 'a' option to add a URL of Regnet API.

```bash
php regnet/regnet.php
```

## Documents ##
[Communication](docs/communication.md)  
[Integration](docs/integration-levels.md)  
[Client](docs/client.md)  
[Server](docs/server.md)  
[API server](docs/api-server.md)  
[Online Documenter](docs/documenter.md)  


