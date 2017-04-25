<?php
declare(strict_types=1);
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

use \mbarquin\reactSlim\ {
    Request\RequestInterface as RequestAdapterInterface,
    Request\SlimRequest,
    Response\SlimResponse,
    Response\ResponseInterface as ResponseAdapterInterface
};
use React\ {
    EventLoop\Factory as ReactEventLoopFactory,
    Http\Response as ReactResponse,
    Http\Request  as ReactRequest,
    Http\Server   as ReactHttpServer,
    Socket\Server as ReactSocketServer
};
use Slim\App as SlimInstance;

/**
 * Instantiates the setup of the reactPHP server and launches it
 */
class Server
{
    /**
     * Reference to a request adapter
     *
     * @var RequestAdapterInterface
     */
    private $requestAdapter = null;

    /**
     * Reference to a response adapter
     *
     * @var ResponseAdapterInterface
     */
    private $responseAdapter = null;

    /**
     * Sets which port will be listened
     *
     * @var int
     */
    private $port = 1337;

    /**
     * Sets which ip will be listened
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * Array with info about partial file uploads
     *
     * @var array
     */
    private $partials = [];

    /**
     * Sets the listened port
     *
     * @param int $port
     *
     * @return self
     */
    public function withPort($port) :self
    {
        if (is_int($port) === true) {
            $this->port = $port;
        }

        return $this;
    }

    /**
     * Sets the listened ip
     *
     * @param int $ip
     *
     * @return self
     */
    public function withHost($ip) :self
    {
        if (empty($ip) === false) {
            $this->host = $ip;
        }

        return $this;
    }

    /**
     * Returns the two callbacks which will process the HTTP call
     *
     * @param SlimInstance $app Slim application instance
     *
     * @return callable
     */
    private function getCallbacks(SlimInstance $app) :callable
    {
        $server = $this;
        return function (
               ReactRequest $request,
               ReactResponse $response
        ) use ($app, $server) {

            $request->on('data', function($body) use ($request, $response, $app, $server) {
                $slRequest  = SlimRequest::createFromReactRequest($request, $body);
                $boundary   = SlimRequest::checkPartialUpload($slRequest);

                $slResponse = SlimResponse::createResponse();

                if($boundary !== false) {
                    if(isset($server->partials[$boundary]) === false) {
                        $server->partials[$boundary]['boundary'] = $boundary;
                    }
                    $continue = SlimRequest::parseBody($body, $server->partials[$boundary]);
                    if ($continue === false) {
                        $filesArr = SlimRequest::getSlimFilesArray($server->partials[$boundary]);

                        $lastRequest = $slRequest
                                ->withUploadedFiles($filesArr)
                                ->withParsedBody($server->partials[$boundary]['fields']);
                        
                        $slResponse  = $app->process($lastRequest, $slResponse);
                        SlimResponse::setReactResponse($response, $slResponse, true);
                    }

                } else {
                    $slResponse = $app->process($slRequest, $slResponse);
                    SlimResponse::setReactResponse($response, $slResponse, true);
                }
            });
        };
    }

    /**
     * Checks Adapters and runs the server with the app
     *
     * @param SlimInstance $app Slim application instance
     *
     * @return void
     */
    public function run(SlimInstance $app)
    {
        $serverCallback = $this->getCallbacks($app);

        // We make the setup of ReactPHP.
        $loop           = ReactEventLoopFactory::create();
        $socket         = new ReactSocketServer($loop);
        $http           = new ReactHttpServer($socket, $loop);

        // Link callback to the Request event.
        $http->on('request', $serverCallback);

        echo "Server running at http://".$this->host.":".$this->port."\n";

        $socket->listen($this->port, $this->host);
        $loop->run();
    }

}
