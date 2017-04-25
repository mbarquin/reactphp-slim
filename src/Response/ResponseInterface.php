<?php
declare(strict_types=1);
/**
 * Request Interface file for an React Adapter request objectv
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
namespace mbarquin\reactSlim\Response;

use Slim\Http\Response;


/**
 * Contract to have a request to adapt a react request object to a new one
 */
interface ResponseInterface
{
    /**
     * It performs the setup of a reactPHP response from another response
     * object and finishes the communication
     *
     * @param \React\Http\Response $reactResp    ReactPHP native response object
     * @param \Slim\Http\Response  $slimResponse SlimPHP native response object
     * @param boolean              $endRequest   If true, response flush will be finished
     *
     * @return void
     */
    static public function setReactResponse(\React\Http\Response $reactResp, \Slim\Http\Response $slimResponse, bool $endRequest = false);

    /**
     * Returns a new response object instance
     *
     * @return \Slim\Http\Response
     */
    static public function createResponse() :Response;
}
