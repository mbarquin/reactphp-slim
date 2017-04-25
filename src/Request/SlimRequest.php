<?php
declare(strict_types=1);
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

use Slim\Http\{
    Request,
    Headers,
    Cookies,
    Uri,
    Body,
    UploadedFile
};

/**
 * Request adapter class file for a React request object
 */
class SlimRequest implements RequestInterface
{

    const HEADERMULTI        = 'multipart/form-data';
    const BOUNDARYSPLIT      = 'boundary=';
    const FIXEDBOUNDARY      = '--';
    const CONTENTDISPOSITION = "\r\nContent-Disposition:";


    /**
     * Returns host name and port as array
     *
     * @param string $reactHead
     *
     * @return array
     */
    static public function getHost(string $reactHead) :array
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
    static public function createFromReactRequest(\React\Http\Request $request, string $body = '') :Request
    {
        $slimHeads = new Headers();
        $cookies   = [];
        $host      = ['', 80];

        foreach($request->getHeaders() as $reactHeadKey => $reactHead) {
            $slimHeads->add($reactHeadKey, $reactHead);
            switch($reactHeadKey) {
                case 'Host':
                    $host = static::getHost($reactHead);
                    break;
                case 'Cookie':
                    $cookies = Cookies::parseHeader($reactHead);
                    break;
            }
        }

        $slimUri = new Uri('http', $host[0], (int)$host[1], $request->getPath(), $request->getQuery());

        $serverParams                    = $_SERVER;
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();

        $slimBody = static::getBody($body);
        return new Request(
                $request->getMethod(), $slimUri, $slimHeads, $cookies,
                $serverParams, $slimBody
            );
    }

    /**
     * Checks if request headers are partial upload headers
     *
     * @param Request $slRequest Slim request object
     *
     * @return bool
     */
    static public function checkPartialUpload(\Slim\Http\Request $slRequest) :bool
    {
        if($slRequest->hasHeader('Content-Type') === true) {
            $contentType = $slRequest->getHeader('Content-Type');

            if(stripos($contentType[0], static::HEADERMULTI) !== false) {
                $posBoundary = stripos($contentType[0], static::BOUNDARYSPLIT);
                $posBoundary = $posBoundary + strlen(static::BOUNDARYSPLIT);

                return static::FIXEDBOUNDARY.substr($contentType[0], $posBoundary);
            }
        }
        return false;
    }

    /**
     * Writes temporary file into tmp folder and returns its name
     *
     * @param string $bodyPart Splitted part from request body
     *
     * @return string Temp file name
     */
    public static function getTmpFile(string $bodyPart) :string
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'React');
        $initPos = strpos($bodyPart, "\r\n\r\n");

        $handle = fopen($temp_file, "w");

        fwrite ($handle, substr($bodyPart, $initPos+4));
        fclose($handle);

        return $temp_file;
    }

    /**
     * Writes data into partial uploads array
     *
     * @param array  $filePartialsInfo Data with current uploaded files
     * @param string $name             HTML Input name
     * @param string $filename         Original file name
     * @param string $contentType      File content type defined by browser
     * @param string $temp_file        Temp file path and name
     *
     * @return void
     */
    static public function writeFilesArray(array &$filePartialsInfo, string $name, string $filename, string $contentType, string $temp_file)
    {
        $index = $name[1];

        $filePartialsInfo['files'][$index]['name']     = $filename[1];
        $filePartialsInfo['files'][$index]['type']     = $contentType[1];
        $filePartialsInfo['files'][$index]['tmp_name'] = $temp_file;

        $filePartialsInfo['last']['type']              = 'files';
        $filePartialsInfo['last']['index']             = $index;
    }

    /**
     * Writes data into a new tmp file
     *
     * @param string $bodyPart Splitted part from body
     * @param string $filename Name and path of temp file
     * @return void
     */
    static public function writeToTmpFile(string $bodyPart, string $filename)
    {
        $handle = fopen($filename, "a");
        fwrite($handle, $bodyPart);
        fclose($handle);
    }

    /**
     * Build an uploaded file objects array(Collection)
     *
     * @param array $filePartialsInfo Data with current uploaded files
     *
     * @return array
     */
    static public function getSlimFilesArray(array $filePartialsInfo) :array
    {
        $ret = [];
        foreach($filePartialsInfo['files']  as $name => $file) {
            $ret[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], UPLOAD_ERR_OK, false);
        }

        return $ret;
    }

    /**
     * Checks parts headers, and parses data in it
     *
     * @param string $bodyPart         Splitted part from body
     * @param array  $filePartialsInfo Data with current uploaded files
     *
     * @return void
     */
    static function parseBodyParts(string $bodyPart, array &$filePartialsInfo)
    {
        preg_match('/^'.static::CONTENTDISPOSITION.' (.*);/', $bodyPart, $contentDispo);

        if (count($contentDispo) > 0) {
            preg_match('/filename="(.*)"/', $bodyPart, $filename);
            if(isset($filename[1]) === true) {
                preg_match('/\r\nContent-Type: (.*)/', $bodyPart, $contentType);
                preg_match('/name=\"(.*)\";/', $bodyPart, $name);

                $temp_file = static::getTmpFile($bodyPart);
                static::writeFilesArray($filePartialsInfo, $name, $filename, $contentType, $temp_file);

            } else {
                preg_match('/name=\"(.*)\"/', $bodyPart, $name);
                $index = $name[1];

                $initPos = strpos($bodyPart, "\r\n\r\n");
                $filePartialsInfo['fields'][$index] = substr($bodyPart, $initPos+4);
                $filePartialsInfo['fields'][$index] = substr($filePartialsInfo['fields'][$index], 0, -2);
            }
        } else {
            if ($filePartialsInfo['last']['type'] === 'files') {
                static::writeToTmpFile($bodyPart, $filePartialsInfo[$filePartialsInfo['last']['type']][$filePartialsInfo['last']['index']]['tmp_name']);
            }
        }
    }

    /**
     * Parses body parts ageter splitting it with boundary string
     *
     * @param string $body             Body received from partial request
     * @param array  $filePartialsInfo Data with current uploaded files
     *
     * @return bool
     */
    static public function parseBody(string $body, array &$filePartialsInfo) :bool
    {
        $bodyParts = explode($filePartialsInfo['boundary'], $body);

        if (empty($bodyParts[0]) === true) {
            array_shift($bodyParts);
        }

        foreach($bodyParts as $piece) {
            if ($piece !== "--\r\n") {
                static::parseBodyParts($piece, $filePartialsInfo);
            }
        }

        if( is_array($bodyParts) === true && in_array("--\r\n", $bodyParts) === true) {
            static::setFileSizes($filePartialsInfo);
            return false;
        } elseif($bodyParts === '--') {
            static::setFileSizes($filePartialsInfo);
            return false;
        }
        return true;
    }

    /**
     * Populates file array with all file sizes
     *
     * @param array $filePartialsInfo Data with current uploaded files
     *
     * @return void
     */
    static public function setFileSizes(array &$filePartialsInfo)
    {
        if(isset($filePartialsInfo['files']) === true && count($filePartialsInfo['files']) > 0) {
            $keys = array_keys($filePartialsInfo['files']);
            foreach($keys as $index) {
                $filePartialsInfo['files'][$index]['size'] = filesize($filePartialsInfo['files'][$index]['tmp_name']);
            }
        }
    }

    /**
     * Returns a Slim body class with data from a react response
     *
     * @param string $body Content of received call
     *
     * @return Body
     */
    static public function getBody(string $body) :Body
    {
        $stream = fopen('php://temp', 'w+');
        if (empty($body) === false) {
            fwrite($stream, $body);
        }
        $slimBody = new Body($stream);
        return $slimBody;
    }
}
