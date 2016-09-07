<?php
namespace mbarquin\reactSlim\Request;

use Slim\Http\Request;
use Slim\Http\Headers;
use Slim\Http\Uri;
use Slim\Http\RequestBody;

class SlimRequest implements RequestInterface
{
    static public function createFromReactRequest(\React\Http\Request $request, $body = '')
    {
        $slimHeads = new Headers();
        foreach($request->getHeaders() as $reactHeadKey => $reactHead) {
            $slimHeads->add($reactHeadKey, $reactHead);
            if($reactHeadKey === 'Host') {
                $host = explode(':', $reactHead);
                if (count($host) === 1) {
                    $host[1] = '80';
                }
            }
        }

        $slimUri = new Uri('http', $host[0], (int)$host[1], $request->getPath(), $request->getQuery());

        $cookies                         = [];
        $serverParams                    = $_SERVER;
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();

        $slimBody = self::getBody($body);
        return new Request(
                $request->getMethod(), $slimUri, $slimHeads, $cookies,
                $serverParams, $slimBody
            );
    }

    static public function getBody($body)
    {
        $slimBody = new RequestBody();

        if (empty($body) === false) {
            $stream = fopen('php://temp', 'w+');
            fwrite($stream, $body);

            $slimBody->attach($stream);
        }
        return $slimBody;
    }
}