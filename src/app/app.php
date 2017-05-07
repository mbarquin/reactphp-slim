<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Slim\Http\ { Request, Response };

$app = new \Slim\App();

$app->get('/', function (Request $request, Response $response) {
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(
            file_get_contents(__DIR__ . '/view/welcome.phtml')
        );
});

$app->get('/hello/{name}', function(Request $request, Response $response) {
    return $response
        ->withHeader('Content-Type', 'text/html')
        ->write(
            sprintf(
                file_get_contents(__DIR__ . '/view/hello.phtml'),
                $request->getAttribute('name') ?
                      ', ' . $request->getAttribute('name')
                    : ''
            )
        );
});

(new \mbarquin\reactSlim\Server())
    ->withHost('0.0.0.0')
    ->withPort(1337)
    ->run($app);