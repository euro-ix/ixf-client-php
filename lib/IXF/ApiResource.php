<?php

namespace IXF;

abstract class ApiResource extends Object
{
    protected static function scopedRetrieve($class, $id, $resource)
    {
        $instance = new $class($id);
        $instance->refresh( $resource );
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
        $this->refreshFrom($response[0]);

        return $this;
    }


    protected static function scopedAll($class, $params=null, $resource)
    {
        self::_validateCall('all', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        $response = $requestor->request('get', $url, $params);

        return Util::convertToIxfObject($response);
    }


    protected static function scopedCreate($class, $params=null, $resource)
    {
        self::_validateCall('create', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        return $requestor->request( 'post', $url, $params );
    }


    protected function scopedSave($class,$resource)
    {
        $requestor = new ApiRequestor();
        $params = $this->serializeParameters();
        self::_validateCall( 'save', $params );
        unset( $params['id'] );

        if( count( $params ) > 0 )
        {
            $url = '/' . $resource . '/' . $this->id;
            return $requestor->request('put', $url, $params);
        }

        return true;
    }

    protected function scopedDelete($class, $params=null,$resource)
    {
        self::_validateCall('delete');
        $requestor = new ApiRequestor();
        $url = '/' . $resource . '/' . $this->id;
        return $requestor->request('delete', $url, $params);
    }

    private static function _validateCall($method, $params=null)
    {
        if ($params !== null && !is_array($params))
            throw new Error( "You must pass an array as the first argument to IXF API method calls." );

        if( $method == 'save' && !isset( $params['id'] ) )
            throw new Error( "Invalid save - object does not have an ID." );
    }


}
