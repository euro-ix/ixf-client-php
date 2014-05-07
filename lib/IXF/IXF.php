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

abstract class IXF
{
    /**
     * @var string The IXF API username to be used for requests.
     */
    public static $apiUser = 'guest';
    /**
     * @var string The IXF API password to be used for requests.
     */
    public static $apiPass = 'guest';

    /**
     * @var string The base URL for the IXF API.
     */
    public static $apiBase = '';
    /**
     * @var string|null The version of the IXF API to use for requests.
     */
    public static $apiVersion = null;
    /**
     * @var boolean Defaults to true.
     */
    public static $verifySslCerts = true;


    public static $debug = false;

    const VERSION = '1.0.0';

    /**
     * @return string The API username used for requests.
     */
    public static function getApiUser()
    {
        return self::$apiUser;
    }

    /**
     * Sets the API username to be used for requests.
     *
     * @param string $apiUser
     */
    public static function setApiUser($apiUser)
    {
        self::$apiUser = $apiUser;
    }

    /**
     * @return string The API password used for requests.
     */
    public static function getApiPass()
    {
        return self::$apiPass;
    }

    /**
     * Sets the API password to be used for requests.
     *
     * @param string $apiPass
     */
    public static function setApiPass($apiPass)
    {
        self::$apiPass = $apiPass;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *                latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return boolean
     */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /**
     * @param boolean $verify
     */
    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }

    /**
     * @return boolean
     */
    public static function getApiBase()
    {
        return self::$apiBase;
    }

    /**
     * @param boolean $apiBase
     */
    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }

    public static function getDebug()
    {
        return self::$debug;
    }

    public static function setDebug( $d )
    {
        self::$debug = $d;
    }
}
