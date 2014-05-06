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
    public function refresh($resource='')
    {
        $url = $this->instanceUrl(null,$resource);

        $requestor = new ApiRequestor();
        $response = $requestor->request(
            'get',
            $url,
            $this->_retrieveOptions
        );
        $this->refreshFrom($response);

        return $this;
    }

    /**
     * @returns string The full API URL for this API resource.
     */
    public function instanceUrl( $type = null, $resource = '' )
    {
        $id = $this['id'];
        $class = get_class($this);

        if (!$id) {
            $message = "Could not determine which URL to request: "
                . "$class instance has invalid ID: $id";
            throw new Error\InvalidRequest($message, null);
        }

        $extn = urlencode( ApiRequestor::utf8($id) );

        switch( $type )
        {
            case 'save':
                return "/update/{$resource}/{$extn}";

            default:
                return "/{$resource}?id=$extn";
        }
    }

    /**
     * @param string $class
     *
     * @returns string The name of the class, with namespacing and underscores
     *    stripped.
     */
    public static function className($class)
    {
        // Useful for namespaces: Foo\IXF_Charge
        if ($postfix = strrchr($class, '\\'))
            $class = substr($postfix, 1);
        if (substr($class, 0, strlen('IXF')) == 'IXF')
            $class = substr($class, strlen('IXF'));
        $class = str_replace('_', '', $class);
        $name = urlencode($class);
        $name = strtolower($name);

        return $name;
    }

    /**
     * @param string $class
     *
     * @returns string The endpoint URL for the given class.
     */
    public static function classUrl($class)
    {
        $base = ucfirst( self::_scopedLsb($class, 'className', $class) );

        return "/${base}s";
    }


    private static function _validateCall($method, $params=null)
    {
        if ($params !== null && !is_array($params)) {
            $message = "You must pass an array as the first argument to IXF API "
                . "method calls.";
            throw new Error($message);
        }
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
        $url = '/create/' . $resource;
        return $requestor->request('post', $url, $params);
    }


    protected function scopedSave($class,$resource)
    {
        self::_validateCall('save');
        $requestor = new ApiRequestor();
        $params = $this->serializeParameters();

        if( count( $params ) > 0 ) {
            $url = '/update/' . $resource . '/' . $this->id;
            $response = $requestor->request('post', $url, $params);

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
}
