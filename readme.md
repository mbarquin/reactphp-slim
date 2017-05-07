reactphp-slim
========

Introduction
------------

This library is created in order to use [ReactPHP](https://github.com/reactphp/react) as a HTTP server for the [Slim PHP framework](https://github.com/slimphp/Slim). It will launch a `Slim\App` process when a request is made, and at the same time it will transfer data from ReactPHP native objects into Slim objects. With this, we will be able to create a basic react server for a Slim PHP framework application.

Data, cookies and file uploads transmission between React and Slim objects. You can access through Slim native functions to uploaded files, data and cookies.

##Installation
You can install the component in the following ways:

* Use the official Github repository (https://github.com/mbarquin/reactphp-slim.git)
* Use composer : `composer require mbarquin/reactphp-slim`

##Usage
After the composer autoload requirement a `Slim\App` should be instanced and prepared as usual. `Slim\App` can be bootstrapped and all dependencies can be injected as you like, after that, a reactphp-slim server should be instanced and call the run method in it, using `Slim\App` as parameter. The reactphp-slim server will act as intermediary and will launch the slim application through the `process` method when requested, this method avoids the usual request and response bootstrap made by Slim.

When uploading files, `move_uploaded_files()` probably won't work, use native object methods to move the file.

```php
require '../vendor/autoload.php';

use \mbarquin\reactSlim;

$app = new \Slim\App();
$app->any('/hello/{name}', function (
    \Slim\Http\Request $request,
    \Slim\Http\Response $response
) {
    return $response
        ->withHeader(
            $request->getHeaders()
        )->write(
            sprintf('Hello %s', $request->getAttribute('name')
        );
});

(new \mbarquin\reactSlim\Server())
    ->withHost('192.168.67.1')
    ->withPort(1337)
    ->run($app);
```

\mbarquin\reactSlim\Server object is the class which is going to configure and launch a ReactPHP server. It has two main methods


**withHost($string)**
Sets the IP to be listened to

**withPort($int)**
Sets the port the server will be listening to, by default it will be set to 1337.

**run(\Slim\App $app)**
It launches the server process and wait until a request is made to launch the \Slim\App passed as parameter.