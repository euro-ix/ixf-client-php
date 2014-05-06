<?php

namespace IXF;

abstract class ApiResource extends Object
{
    protected static function scopedRetrieve($class, $id, $resource)
    {
        $instance = new $class($id);
        $instance->refresh($resource);
        return $instance;
    }

    /**
     * @returns IXF_ApiResource The refreshed resource.
     */
    public function refresh($resource)
    {
        $url = $url = '/' . $resource . '/' . $this->id;

        $requestor = new ApiRequestor();
        $response = $requestor->request(
            'get',
            $url,
            $this->_retrieveOptions
        );
        $this->refreshFrom($response);

        return $this;
    }


    protected static function scopedAll($class, $params=null, $resource)
    {
        self::_validateCall('all', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        $response = $requestor->request('get', $url, $params);

        if( isset( $response[ 'error' ] ) )
            return( $response );

        return Util::convertToIxfObject($response);
    }


    protected static function scopedCreate($class, $params=null, $resource)
    {
        self::_validateCall('create', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        return $requestor->request('post', $url, $params);
    }


    protected function scopedSave($class,$resource)
    {
        self::_validateCall('save');
        $requestor = new ApiRequestor();
        $params = $this->serializeParameters();

        if( count( $params ) > 0 ) {
            $url = '/' . $resource . '/' . $this->id;
            $response = $requestor->request('put', $url, $params);

            if( isset( $response['error'] ) )
                return $response;
        }

        return true;
    }

    protected function scopedDelete($class, $params=null,$resource)
    {
        self::_validateCall('delete');
        $requestor = new ApiRequestor();
        $url = '/' . $resource . '/' . $this->id;
        $response = $requestor->request('delete', $url, $params);

        if( is_array( $response ) && count( $response ) == 0 ) {
            $this->refreshFrom($response);
            return true;
        }

        return $response;
    }

    private static function _validateCall($method, $params=null)
    {
        if ($params !== null && !is_array($params)) {
            $message = "You must pass an array as the first argument to IXF API "
                . "method calls.";
            throw new Error($message);
        }
    }


}
