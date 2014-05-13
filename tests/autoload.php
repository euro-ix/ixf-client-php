<?php

require_once __DIR__ . "/../vendor/autoload.php";


// set the API endpoint and authentication parameters
IXF\IXF::setApiBase( 'https://ixf.20c.com:7010/api' );
IXF\IXF::setApiUser( 'guest' );
IXF\IXF::setApiPass( 'guest' );
IXF\IXF::setDebug( false );
IXF\IXF::setVerifySslCerts( false );
