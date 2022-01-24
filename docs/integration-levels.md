# Integration level #

According to the needs of the project, integration can be done that covers only some elements of the communication. Three levels of integration can be specified. Each higher level encompasses all the elements of previous levels.

## Level 1 - Response integration ##

At this level, only the response from the server is normalized. To prepare the standardized response you can use the class Regnet\Server\Response.

An example of implementation of this level can be found in the document: [Response integration](../sample/response.php) 

## Level 2 - Request integration ##

At this level of integration, in addition to the response, the request to the server is also normalized. Level 2 integration can be implemented using classes:  

On the client side:  
Regnet\Client\Request  

On the server side:  
Regnet\Server\Response  
Regnet\Server\Request  

At this level, the authentication mechanism through API key is also standardized.  

Regnet libraries at this level provide the following functionalities:  

* Standardization of communication: client -> server -> client  
* Authentication through API key  
* Access control according to the client's IP address  

An example of implementation of this level can be found in the document: [Request integration](../sample/request.php) 

## Level 3 - API server integration ##

At this level of integration, communication based on the RPC (remote procedure call protocol) model is being standardized. Moreover, a mechanism is provided to describe the classes and methods exported through the API. Level 3 integration can be implemented using classes:  

On the client side:  
Regnet\Client\ClassMapper  

On the server side:  
Regnet\Server\Server  
Regnet\Server\Explorer  

Regnet libraries at this level provide the following functionalities:  

* Central management of exported classes and methods (on the server side)  
* Central security policy (on the server side)  
* Automated mapping for the exported classes and methods (on the client side)  
* Cache on both the server side and the client side  
* Object Oriented RPC engine  
* Online tool to explore and document API  

The samples of implementation, you can see on the following documents: [Implementation of API endpoint](../sample/api.php)  

