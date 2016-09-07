reactphp-slim
========

Introduction
------------

This library is created in order to use reactPHP as a HTTP server for Slim framework, I have extended Slim request and response objects to implement functions which allows us to transfer data from native reactPHP objects into Slim objects. With this we will be able to create a basic react server for Slim framework.

Now working in a new way to setup the reactPHP server and run the Slim Application, please use v0.4.2 release (doc bellow), this version is not finished yet, I'm testing it.


```php
require '../vendor/autoload.php';

use \mbarquin\reactSlim;

// We keep a new Slim app instance.
$app = new \Slim\App();

// We add a closure to attend defined request routes
$app->any('/hello/{name}', function (
        \Slim\Http\Request $request,
        \Slim\Http\Response $response) {

        $name = $request->getAttribute('name');
        $response->getBody()->write("Hello, $name");

        return $response;
    });

$server = new \mbarquin\reactSlim\Server();

$server->withPort(1337)->run($app);
```


### v0.4.2 Setup
This is the old setup to run the reactPHP server with a slimPHP application

```php
require '../vendor/autoload.php';

use mbarquin\reactSlim;

// We keep a new Slim app instance.
$app = new \Slim\App();

// We add a closure to listen defined request routes
$app->get('/hello/{name}', function (
        \mbarquin\reactSlim\Request $request,
        \mbarquin\reactSlim\Response $response) {

        $name = $request->getAttribute('name');
        $response->getBody()->write("Hello, $name");

        return $response;
    });

// We create a closure to be attached to server request event.
$serverCallback = function (
       \React\Http\Request $request,
       \React\Http\Response $response) use ($app){

    $slRequest  = \mbarquin\reactSlim\Request::createFromReactRequest($request);
    $slResponse = new \mbarquin\reactSlim\Response();

    $app->process($slRequest, $slResponse);

    $slResponse->setReactResponse($response, true);
};

// We make the setup of the ReactPHP
$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket, $loop);

// Ligamos la closure al evento request.
$http->on('request', $serverCallback);

echo "Server running at http://127.0.0.1:1337\n";

$socket->listen(1337);
$loop->run();
```