<?php
declare(strict_types=1);
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

use Slim\Http\ {
    Headers as SlimPHPHeaders,
    Uri as SlimPHPUri,
    Request as SlimPHPRequest,
    RequestBody as SlimPHPRequestBody
};
use React\Http\Request as ReactRequest;

/**
 * Legacy request adapter class file for a React request object
 */
class Request extends SlimPHPRequest
{
    /**
     * Creates a new request object from the data of a reactPHP request object
     *
     * @param ReactRequest $request ReactPHP native request object
     *
     * @return self
     */
    static public function createFromReactRequest(ReactRequest $request) :self
    {
        $slimHeads = new SlimPHPHeaders();
        foreach($request->getHeaders() as $reactHeadKey => $reactHead) {
            $slimHeads->add($reactHeadKey, $reactHead);
            if($reactHeadKey === 'Host') {
                $host = explode(':', $reactHead);
                if(count($host) === 1) {
                    $host[1] = '80';
                }
            }
        }

        $slimUri = new SlimPHPUri('http', $host[0], (int)$host[1], $request->getPath(), $request->getQuery());

        $cookies = [];
        $serverParams = $_SERVER;
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();

        $slimBody = new SlimPHPRequestBody();

        return new self(
                $request->getMethod(), $slimUri, $slimHeads, $cookies,
                $serverParams, $slimBody
            );
    }
}