<?php
/**
 * Request adapter class file for a React request object
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
namespace mbarquin\reactSlim\Request;

use Slim\Http\Request;
use Slim\Http\Headers;
use Slim\Http\Cookies;
use Slim\Http\Uri;
use Slim\Http\Body;

/**
 * Request adapter class file for a React request object
 */
class SlimRequest implements RequestInterface
{

    static public function getHost($reactHead)
    {
        $host = explode(':', $reactHead);
        if (count($host) === 1) {
            $host[1] = '80';
        }
        return $host;
    }

    /**
     * Creates a new request object from the data of a reactPHP request object
     *
     * @param \React\Http\Request $request ReactPHP native request object
     * @param string              $body    Content of received call
     *
     * @return \Slim\Http\Request
     */
    static public function createFromReactRequest(\React\Http\Request $request, $body = '')
    {
        $slimHeads = new Headers();
        $cookies   = [];
        $host      = ['', 80];

        foreach($request->getHeaders() as $reactHeadKey => $reactHead) {
            $slimHeads->add($reactHeadKey, $reactHead);
            switch($reactHeadKey) {
                case 'Host':
                    $host = self::getHost($reactHead);
                    break;
                case 'Cookie':
                    $cookies = Cookies::parseHeader($reactHead);
                    break;
            }
        }

        $slimUri = new Uri('http', $host[0], (int)$host[1], $request->getPath(), $request->getQuery());

        $serverParams                    = $_SERVER;
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();

        $slimBody = self::getBody($body);
        return new Request(
                $request->getMethod(), $slimUri, $slimHeads, $cookies,
                $serverParams, $slimBody
            );
    }

    /**
     * Returns a Slim body class with data from a react response
     *
     * @param string $body Content of received call
     *
     * @return \Slim\Http\RequestBody
     */
    static public function getBody($body)
    {
        $stream = fopen('php://temp', 'w+');
        if (empty($body) === false) {
            fwrite($stream, $body);
        }
        $slimBody = new Body($stream);
        return $slimBody;
    }
}
