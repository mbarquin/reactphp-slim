<?php
namespace mbarquin\reactSlim;

use \mbarquin\reactSlim\Request\SlimRequest;
use \mbarquin\reactSlim\Response\SlimResponse;

class Server
{
    /**
     * Reference to a request adapter
     *
     * @var RequestInterface
     */
    private $requestAdapter = null;


    /**
     * Reference to a response adapter
     *
     * @var ResponseInterface
     */
    private $responseAdapter = null;

    /**
     * Sets which port will be listened
     *
     * @var int
     */
    private $port = 1337;


    /**
     * Sets the listened port
     *
     * @param type $port
     *
     * @return \mbarquin\reactSlim\Server
     */
    public function withPort($port)
    {
        if (is_int($port) === true) {
            $this->port = $port;
        }

        return $this;
    }

    private function getCallbacks($app)
    {
        return function (
               \React\Http\Request $request,
               \React\Http\Response $response) use ($app){

            $request->on('data', function($body) use ($request, $response, $app) {

                $slRequest  = SlimRequest::createFromReactRequest($request, $body);
                $slResponse = SlimResponse::createResponse();

                $app->process($slRequest, $slResponse);

                SlimResponse::setReactResponse($response, $slResponse, true);
            });
        };
    }

    /**
     * Checks Adapters and runs the server with the app
     */
    public function run(\Slim\App $app)
    {
        $serverCallback = $this->getCallbacks($app);

        // We make the setup of ReactPHP.
        $loop           = \React\EventLoop\Factory::create();
        $socket         = new \React\Socket\Server($loop);
        $http           = new \React\Http\Server($socket, $loop);

        // Ligamos la closure al evento request.
        $http->on('request', $serverCallback);

        echo "Server running at http://127.0.0.1:1337\n";

        $socket->listen($this->port);
        $loop->run();
    }

}

