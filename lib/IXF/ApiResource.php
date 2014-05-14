<?php

namespace IXF;

abstract class ApiResource extends Object
{
    /**
     * If a class exists for the given resource return an instance
     * of it, else return an instance of the generic ApiResouce class.
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @param int    $id       ID of the object - use for initialisation
     * @return ApiResouce Or the more specific child class
     */
    public static function resolveClass( $resource, $id = null )
    {
        $class = 'IXF\\' . $resource;
        if( class_exists($class) )
            return new $class( $id );

        return new IXF\ApiResource( $id );
    }

    /**
     * If a class exists for the given resource return the name
     * of it, else return the generic ''IXF\ApiResouce''.
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @return string ''IXF\ApiResource'' or the more specific child class
     */
    public static function resolveClassname( $resource )
    {
        $class = 'IXF\\' . $resource;
        if( class_exists($class) )
            return $class;

        return 'IXF\\ApiResource';
    }

    /**
     * Retrieve an object by ID
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @param int    $id       ID of the object
     * @return ApiResouce
     * @throws Error\NotFound If the requested object does not exist
     */
    public static function retrieve( $resource, $id )
    {
        $instance = self::resolveClass( $resource, $id );
        $instance->refresh( $resource );
        return $instance;
    }


    /**
     * Refresh the current object from the API service
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @return ApiResource The refreshed resource.
     * @throws Error An appropriate error object
     */
    public function refresh( $resource )
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


    /**
     * Get all objects of a given resource
     *
     * Allowed params:
     * * ''limit'' => integer, limit the number of objects
     * * ''skip'' => integer, skip the first n objects
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @param array|null $params
     * @return ApiResource[] An array of resource objects.
     * @throws Error An appropriate error object
     */
    public static function all( $resource, $params=null )
    {
        self::_validateCall('all', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        $response = $requestor->request('get', $url, $params);
        return Util::convertToIxfObject($response);
    }




    /**
     * Create an object from a given array of key/value pairs
     *
     * @param string $resource The resource - e.g. ''IXP''
     * @param array|null $params
     * @return int The created object ID
     * @throws Error An appropriate error object
     */
    public static function create( $resource, $params=null )
    {
        self::_validateCall('create', $params);
        $requestor = new ApiRequestor();
        $url = '/' . $resource;
        return $requestor->request( 'post', $url, $params );
    }

    /**
     * Save an object
     *
     * @return bool True when successful
     * @throws Error An appropriate error object
     */
    public function save()
    {
        $requestor = new ApiRequestor();
        $params = $this->serializeParameters();
        self::_validateCall( 'save', $params );
        unset( $params['id'] );

        if( count( $params ) > 0 )
        {
            $url = '/' . $this->getType() . '/' . $this->id;
            return $requestor->request('put', $url, $params);
        }

        return true;
    }



    /**
     * Delete an object
     *
     * @return bool True when successful
     * @throws Error An appropriate error object
     */
    public function delete($params=null)
    {
        self::_validateCall('delete');
        $requestor = new ApiRequestor();
        $url = '/' . $this->getType() . '/' . $this->id;
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
