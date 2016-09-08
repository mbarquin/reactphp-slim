<?php
namespace mbarquin\reactSlim;

class Response extends \Slim\Http\Response
 {
    public function setReactResponse(\React\Http\Response $reactResp, $endRequest = false)
    {
        $reactResp->writeHead($this->getStatusCode(), $this->getHeaders());
        $reactResp->write($this->getBody());

        if ($endRequest === true) {
            $reactResp->end();
        }
    }
}