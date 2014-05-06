<?php

namespace IXF\Error;
use IXF\Error as Error;

class InvalidRequest extends Error
{
    public function __construct($message, $param, $httpStatus=null,$httpBody=null, $jsonBody=null)
    {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
        $this->param = $param;
    }
}
