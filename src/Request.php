<?php
/**
 * Legacy request adapter class file for a React request object
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

use Slim\Http\Headers;
use Slim\Http\Uri;
use Slim\Http\RequestBody;

/**
 * Legacy request adapter class file for a React request object
 */
class Request extends \Slim\Http\Request
{
    /**
     * Creates a new request object from the data of a reactPHP request object
     *
     * @param \React\Http\Request $request ReactPHP native request object
     *
     * @return \Slim\Http\Request
     */
    static public function createFromReactRequest(\React\Http\Request $request)
    {
        $slimHeads = new Headers();
        foreach($request->getHeaders() as $reactHeadKey => $reactHead) {
            $slimHeads->add($reactHeadKey, $reactHead);
            if($reactHeadKey === 'Host') {
                $host = explode(':', $reactHead);
                if(count($host) === 1) {
                    $host[1] = '80';
                }
            }
        }

        $slimUri = new Uri('http', $host[0], (int)$host[1], $request->getPath(), $request->getQuery());

        $cookies = [];
        $serverParams = $_SERVER;
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();

        $slimBody = new RequestBody();

        return new self(
                $request->getMethod(), $slimUri, $slimHeads, $cookies,
                $serverParams, $slimBody
            );
    }
}