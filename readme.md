reactphp-slim
========

Introduction
------------

This library is created in order to use reactPHP as a HTTP server for Slim framework. It will launch a Slim\App process when a request is made, and at the same time it will transfer data from reactPHP native objects into Slim objects. With this, we will be able to create a basic react server for a Slim framework application.

Data, cookies and file uploads transmission between react and Slim objects. You can access through slim native functions to uploaded files, data and cookies.

##Installation
You can install the component in the following ways:

* Use the official Github repository (https://github.com/mbarquin/reactphp-slim.git)
* Use composer : composer require mbarquin/reactphp-slim --dev

##Usage
After the composer autoload requirement a Slim\App should be instanced and prepared as usual. Slim\App can be bootstrapped and all dependencies can be injected as you like, after that, a reactphp-slim server should be instanced and call the run method in it, using slim\App as parameter. The reactphp-slim server will act as intermediary and will launch the slim application through the ``process`` method when requested, this method avoids the usual request and response bootstrap made by Slim.

When uploading files, move_uploaded_files() probably won't work, use native object methods to move the file.

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
        $response->getBody()->write(print_r($request->getParsedBody(), true));
        $response->getBody()->write(print_r($request->getCookieParams(), true));
        $response->getBody()->write(print_r($request->getHeaders(), true));
        // $response->getBody()->write(print_r($request->getUploadedFiles(), true));

        return $response;
    });

$server = new \mbarquin\reactSlim\Server();

$server->withHost('192.168.67.1')->withPort(1337)->run($app);
```

\mbarquin\reactSlim\Server object is the class which is going to configure and launch a ReactPHP server. It has two main methods


**withHost($string)**
Sets the IP to be listened to

**withPort($int)**
Sets the port the server will be listening to, by default it will be set to 1337.

**run(\Slim\App $app)**
It launches the server process and wait until a request is made to launch the \Slim\App passed as parameter.


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