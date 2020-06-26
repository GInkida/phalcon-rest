<?php

namespace PhalconRest\Export\Documentation;

use PhalconRest\Api\ApiRequest;

class ApiEndpoint
{
    protected $name;
    protected $description;
    protected $httpMethod;
    protected $path;
    protected $exampleResponse;
    protected $allowedRoles = [];

    /**
     * @var ApiRequest
     */
    protected $request;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getExampleResponse()
    {
        return $this->exampleResponse;
    }

    public function setExampleResponse($exampleResponse)
    {
        $this->exampleResponse = $exampleResponse;
    }

    public function getAllowedRoles()
    {
        return $this->allowedRoles;
    }

    public function setAllowedRoles($allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
