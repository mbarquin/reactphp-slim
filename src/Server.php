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
 * @author     Nigel Greenway <github@futurepixels.co.uk>
 * @copyright  (c) 2016, Moisés Barquín <moises.barquin@gmail.com>
 * @version    GIT: $Id$
 */
namespace mbarquin\reactSlim;

use \mbarquin\reactSlim\Request\SlimRequest;
use \mbarquin\reactSlim\Response\SlimResponse;
use React\Http\Request;
use React\Http\Response;
use Slim\App;


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
     * Sets which ip will be listened
     * @var type
     */
    private $host = '127.0.0.1';

    /**
     * @var string $webRoot
     */
    private $webRoot;

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
     *
     *
     * @param string $directory
     *
     * @return \mbarquin\reactSlim\Server
     */
    public function withWebRoot($directory)
    {
        if (
            empty($directory) === false
            && is_dir($directory) === true
        ) {
            $this->webRoot = $directory;
        }

        return $this;
    }

    /**
     * Sets the listened ip
     *
     * @param int $ip
     *
     * @return \mbarquin\reactSlim\Server
     */
    public function withHost($ip)
    {
        if (empty($ip) === false) {
            $this->host = $ip;
        }

        return $this;
    }

    /**
     * Returns the two callbacks which will process the HTTP call
     *
     * @param \Slim\App $app Slim application instance
     *
     * @return void
     */
    private function bootApp(\Slim\App $app, Request $request, Response $response)
    {
        $server = $this;
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
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return void
     */
    private function serveStatic(Request $request, Response $response)
    {
        $body = file_get_contents($this->webRoot . '/' . $request->getPath());
        $response->writeHead(['Content-Type' => $request->getHeaders()['Accept'][0]]);
        $response->end(
            $body
        );
    }

    /**
     * Checks Adapters and runs the server with the app
     *
     * @param \Slim\App $app Slim application instance
     *
     * @return void
     */
    public function run(\Slim\App $app)
    {
        // We make the setup of ReactPHP.
        $loop   = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($loop);
        $http   = new \React\Http\Server($socket, $loop);

        if ($this->webRoot !== null) {
            // Link callback to the Request event.
            $http->on('request', function (Request $request, Response $response) use ($app) {
                // POSSIBLY ADD MORE ITEMS?
                if (preg_match('/\.(?:css|png|jpg|jpeg|gif)$/', $request->getPath())) {
                    $this->serveStatic($request, $response);
                } else {
                    $this->bootApp($app, $request, $response);
                }
            });
            echo sprintf(
                "Server running at http://%s:%d\nAssets are being served from %s\n",
                $this->host,
                $this->port,
                $this->webRoot
            );
        } else {
            $http->on('request', function (Request $request, Response $response) use ($app) {
                $this->bootApp($app, $request, $response);
            });
            echo sprintf(
                "Server running at http://%s:%s\n",
                $this->host,
                $this->port
            );
        }

        $socket->listen($this->port, $this->host);
        $loop->run();
    }

}
