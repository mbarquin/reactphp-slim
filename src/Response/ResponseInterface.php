<?php
/**
 * Request Interface file for an React Adapter request object
 */
namespace mbarquin\reactSlim\Response;
/**
 * Contract to have a request to adapt a react request object to a new one
 */
interface ResponseInterface
{
    static public function setReactResponse(\React\Http\Response $reactResp, \Slim\Http\Response $slimResponse, $endRequest = false);
    static public function createResponse();
}
