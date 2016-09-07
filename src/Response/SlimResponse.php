<?php
namespace mbarquin\reactSlim\Response;

class SlimResponse implements ResponseInterface
{
    static function setReactResponse(\React\Http\Response $reactResp, \Slim\Http\Response $slimResponse, $endRequest = false)
    {
        $reactResp->writeHead(
                $slimResponse->getStatusCode(), $slimResponse->getHeaders()
            );

        $reactResp->write($slimResponse->getBody());

        if ($endRequest === true) {
            $reactResp->end();
        }
    }

    static public function createResponse()
    {
        return new \Slim\Http\Response();
    }
}