<?php
namespace mbarquin\reactSlim;

use Slim\Http\Headers;
use Slim\Http\Uri;
use Slim\Http\RequestBody;

class Request extends \Slim\Http\Request
{
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