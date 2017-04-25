<?php
declare(strict_types=1);
/**
 * Legacy Response adapter class file.
 * It performs the setup of a reactPHP response and finishes the communication
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

/**
 * Response adapter class
 * It performs the setup of a reactPHP response and finishes the communication
 */
class Response extends \Slim\Http\Response
 {
    /**
     * It performs the setup of a reactPHP response from a SlimpPHP response
     * object and finishes the communication
     *
     * @param \React\Http\Response $reactResp    ReactPHP native response object
     * @param bool                 $endRequest   If true, response flush will be finished
     *
     * @return void
     */
    public function setReactResponse(\React\Http\Response $reactResp, bool $endRequest = false)
    {
        $reactResp->writeHead($this->getStatusCode(), $this->getHeaders());
        $reactResp->write($this->getBody());

        if ($endRequest === true) {
            $reactResp->end();
        }
    }
}