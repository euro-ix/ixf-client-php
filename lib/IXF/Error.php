<?php

namespace IXF;

class Error extends \Exception
{

    const ERROR_UNKNOWN_TYPE        = 101;
    const ERROR_OBJECT_NOT_FOUND    = 102;
    const ERROR_MISSING_ARGUMENTS   = 103;
    const ERROR_USAGE               = 104;
    const ERROR_INTERNAL            = 500;
    const ERROR_AUTHENTICATION      = 401;
    

    public function __construct($message, $httpStatus=null, $httpBody=null, $jsonBody=null)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->httpBody = $httpBody;
        $this->jsonBody = $jsonBody;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getHttpBody()
    {
        return $this->httpBody;
    }

    public function getJsonBody()
    {
        return $this->jsonBody;
    }
}
