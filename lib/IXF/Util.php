<?php

/*
* IXF Database - PHP Client
*
* Based on the Stripe API client.
*
* See: https://github.com/euro-ix/ixf-client-php
* License: MIT
*/

namespace IXF;

abstract class Util
{
    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if( !is_array( $array ) )
            return false;

        // using Stripe's library as a base for this was a cluster fuck of an idea.
        // why? figure this out:
        $keys = array_keys( $array );
        foreach( array_keys( $array ) as $i )
            if( !is_numeric( $i ) )
                return false;

        return true;
    }

    /**
     * Recursively converts the PHP IXF object to an array.
     *
     * @param array $values The PHP IXF object to convert.
     * @return array
     */
    public static function convertIxfObjectToArray($values)
    {
        $results = array();
        foreach ($values as $k => $v)
        {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof Object) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertIxfObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }

        return $results;
    }

    /**
    * Converts a response from the IXF API to the corresponding PHP object.
    *
    * @param array $resp The response from the IXF API.
    * @return Object|array
    */
    public static function convertToIxfObject($resp)
    {
        if( self::isList( $resp ) )
        {
            $mapped = array();

            foreach( $resp as $k => $i )
                array_push( $mapped, self::convertToIxfObject($i) );

            return $mapped;
        }
        elseif( is_array( $resp ) )
        {
            $type = null;
            if( isset( $resp['_id'] ) )
                $type = substr( $resp['_id'], 0, strpos( $resp['_id'], "." ) );

            $class = ApiResource::resolveClassName( $type );
            return Object::scopedConstructFrom($class, $resp);
        }
        else
            return $resp;
    }

    public static function randomString( $length = 12 )
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for( $i = 0; $i < $length; $i++ )
            $randomString .= $chars[ rand( 0, strlen( $chars ) - 1 ) ];

        return $randomString;
    }
}
