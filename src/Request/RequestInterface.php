<?php
/**
 * Request Interface file for an React Adapter request object
 */
namespace mbarquin\reactSlim\Request;
/**
 * Contract to have a request to adapt a react request object to a new one
 */
interface RequestInterface
{
    static public function createFromReactRequest(\React\Http\Request $request);
}
