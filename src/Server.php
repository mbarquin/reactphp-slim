<?php
/**
 * Server launcher class file. It makes the setup of the reactPHP server
 *
 * (c) Moisés Barquín <moises.barquin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * PHP version 7.0
 *
 * @package    reactSlim
 * @subpackage reactSlim
 * @author     Moises Barquin <moises.barquin@gmail.com>
 * @copyright  (c) 2016, Moisés Barquín <moises.barquin@gmail.com>
 * @version    GIT: $Id$
 */
namespace mbarquin\reactSlim;

use \mbarquin\reactSlim\Request\SlimRequest;
use \mbarquin\reactSlim\Response\SlimResponse;

/**
 * Server launcher class. It makes the setup of the reactPHP server
 * and launchs it
 */
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
     * @param int $port
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

    /**
     * Returns the two callbacks which will process the HTTP call
     *
     * @param \Slim\App $app Slim application instance
     *
     * @return callable
     */
    private function getCallbacks(\Slim\App $app)
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
     *
     * @param \Slim\App $app Slim application instance
     *
     * @return callable
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
