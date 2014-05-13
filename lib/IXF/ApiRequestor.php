<?php

namespace IXF;

class ApiRequestor
{
    private static function blacklistedCerts()
    {
        return array();
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @returns string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if ( is_string($value) && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * @param string     $method
     * @param string     $url
     * @param array|null $params
     *
     * @return array An array whose first element is the response and second
     *               element is the API key used to make the request.
     */
    public function request($method, $url, $params=null)
    {
        if( $params === null )
            $params = array();

        list( $rbody, $rcode ) = $this->requestRaw( $method, $url, $params );

        $resp = $this->interpretResponse( $rbody, $rcode, $method );

        return $resp['data'];
    }

    /**
     * @param string $rbody A JSON string.
     * @param int    $rcode
     * @param array  $resp
     *
     * @throws Error\InvalidRequest if the error is caused by the user.
     * @throws Error\Authentication if the error is caused by a lack of
     *                              permissions.
     * @throws Error\Api            otherwise.
     */
    public function handleApiError( $rbody, $rcode, $resp )
    {
        if( !is_array( $resp ) || !isset( $resp['meta'] ) )
        {
            $msg = "Invalid response object from API: $rbody "
                . "(HTTP response code was $rcode)";

            throw new Error\Api($msg, $rcode, $rbody, $resp);
        }

        $error = $resp['meta']['error'];
        $msg   = isset( $error['message'] ) ? $error['message'] : null;
        $param = isset( $error['param']   ) ? $error['param']   : null;
        $code  = isset( $error['code']    ) ? $error['code']    : null;

        switch( $rcode )
        {
            case 404:
                throw new Error\NotFound( $msg, $param, $rcode, $rbody, $resp );

            case 401:
                throw new Error\AuthenticationError( $msg, $rcode, $rbody, $resp );

            default:
                throw new Error\Api( $msg, $rcode, $rbody, $resp );
        }
    }

    private function requestRaw( $method, $url, $params )
    {
        if( is_array( $params ) && count( $params ) && $method != 'get' )
            $params = json_encode( $params );

        $ua = array(
            'bindings_version' => IXF::VERSION,
            'lang' => 'php',
            'lang_version' => phpversion(),
            'publisher' => 'IXF',
            'uname' => php_uname()
        );

        $headers = array(
            'X-IXF-Client-User-Agent: ' . json_encode($ua),
            'User-Agent: IXF/v1 PhpBindings/' . IXF::VERSION
        );

        return $this->curlRequest(
            $method,
            IXF::$apiBase . $url,
            $headers,
            $params
        );
    }

    private function interpretResponse( $rbody, $rcode, $method )
    {
        try
        {
            $resp = json_decode( $rbody, true );
        }
        catch( Exception $e )
        {
            throw new Error\Api(
                "Invalid response body from API: $rbody (HTTP response code was $rcode)",
                $rcode, $rbody
            );
        }

        if( $rcode < 200 || $rcode >= 300 )
            $this->handleApiError( $rbody, $rcode, $resp );

        switch( $method )
        {
            case 'post':
                if( isset( $resp['meta']['url'] ) )
                {
                    // create - send back the new ID
                    return [ 'data' => substr( $resp['meta']['url'], strrpos( $resp['meta']['url'], '/' ) + 1 ) ];
                }

            case 'put':
            case 'delete':
                return [ 'data' => true ];
        }

        return $resp;
    }

    private function curlRequest( $method, $absUrl, $headers, $params )
    {
        $curl = curl_init();
        $method = strtolower( $method );
        $opts = array();

        $headers[] = 'Accept: application/json';

        switch( $method ) {
            case 'get':
                $opts[CURLOPT_HTTPGET] = 1;
                foreach( $params as $k => $v ) {
                    $absUrl .= ( ( strpos( $absUrl, '?' ) === false ) ? '?' : '&' ) . urlencode( $k ) . '=' . urlencode( $v );
                }
                break;

            case 'post':
                $headers[] = 'Content-length: ' . strlen( $params );
                $headers[] = 'Content-type: application/json';
                $opts[CURLOPT_POSTFIELDS]  = $params;
                $opts[CURLOPT_POST] = 1;
                break;

            case 'put':
                $opts[CURLOPT_POSTFIELDS]    = $params;
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $headers[] = 'Content-length: ' . strlen( $params );
                $headers[] = 'Content-type: application/json';
                break;

            case 'delete':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;

            default:
                throw new Error\Api("Unrecognized method $method");
        }

        $absUrl = self::utf8($absUrl);
        $opts[CURLOPT_URL] = $absUrl;

        if( IXF::getDebug() )
            echo "\n" . strtoupper( $method ) . ": {$absUrl}";

        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        $opts[CURLOPT_TIMEOUT] = 80;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;

        $opts[CURLOPT_USERPWD] = IXF::getApiUser() . ':' . IXF::getApiPass();
        $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;

        if (!IXF::$verifySslCerts)
            $opts[CURLOPT_SSL_VERIFYPEER] = false;

        curl_setopt_array($curl, $opts);
        $rbody = curl_exec($curl);

        if (!defined('CURLE_SSL_CACERT_BADFILE')) {
            define('CURLE_SSL_CACERT_BADFILE', 77);  // constant not defined in PHP
        }

        if ($rbody === false) {
            $errno = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($errno, $message);
        }

        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return array($rbody, $rcode);
    }

    /**
    * @param number $errno
    * @param string $message
    * @throws IXF_ApiConnectionError
    */
    public function handleCurlError($errno, $message)
    {
        $apiBase = IXF::$apiBase;
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
            $msg = "Could not connect to IXF ($apiBase).  Please check your "
            . "internet connection and try again.";
            break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
            $msg = "Could not verify IXF's SSL certificate.  Please make sure "
            . "that your network is not intercepting certificates.  "
            . "(Try going to $apiBase in your browser.)  ";
            break;
            default:
            $msg = "Unexpected error communicating with IXF.  ";
        }

        $msg .= "\n\n(Network error [errno $errno]: $message)";
        throw new Error\ApiConnection($msg);
    }

    private function checkSslCert($url)
    {
        return true;
    }

    /* Checks if a valid PEM encoded certificate is blacklisted
    * @return boolean
    */
    public static function isBlackListed($certificate)
    {
        $certificate = trim($certificate);
        $lines = explode("\n", $certificate);

        // Kludgily remove the PEM padding
        array_shift($lines); array_pop($lines);

        $der_cert = base64_decode(implode("", $lines));
        $fingerprint = sha1($der_cert);

        return in_array($fingerprint, self::blacklistedCerts());
    }

}
