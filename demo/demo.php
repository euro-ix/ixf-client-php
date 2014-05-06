#! /usr/local/bin/php
<?php

// Demo / test file for the IXF API PHP Client

// these includes should not be required when using composer
require_once __DIR__ . "/../lib/IXF/IXF.php";
require_once __DIR__ . "/../lib/IXF/ApiRequestor.php";
require_once __DIR__ . "/../lib/IXF/Util.php";
require_once __DIR__ . "/../lib/IXF/Util/Set.php";
require_once __DIR__ . "/../lib/IXF/Object.php";
require_once __DIR__ . "/../lib/IXF/ApiResource.php";
require_once __DIR__ . "/../lib/IXF/AttachedObject.php";
require_once __DIR__ . "/../lib/IXF/IXP.php";
require_once __DIR__ . "/../lib/IXF/Error.php";
require_once __DIR__ . "/../lib/IXF/Error/Api.php";
require_once __DIR__ . "/../lib/IXF/Error/ApiConnection.php";
require_once __DIR__ . "/../lib/IXF/Error/InvalidRequest.php";


// set the API endpoint and authentication parameters
IXF\IXF::setApiBase( 'https://dev0.lo0.20c.com:7010/api' );
IXF\IXF::setApiUser( 'guest' );
IXF\IXF::setApiPass( 'guest' );
IXF\IXF::setVerifySslCerts( false );


// find five IXPs starting at 10
$objs = IXF\IXP::all( [ 'skip' => 10, 'limit' => 5 ] );

if( is_array( $objs ) && isset( $objs['error'] ) )
{
    var_dump( $objs );
    die();
}

foreach( $objs as $o )
    echo $o->full_name . '[' . $o->short_name . '] => '
        . $o->lat . ', ' . $o->lon . "\n";

// let's edit the last one from above

echo "ID/Short name before:  " . $o->id . '/' . $o->short_name . "\n";
$old_short_name = $o->short_name;
$o->short_name = 'EDITED';

// returns true on success (or no updates needed)
if( ( $err = $o->save() ) !== true )
{
    // on failure, an array of the form:
    // array(1) {
    //  ["error"]=>
    //  string(49) "DoesNotExist: Ixps matching query does not exist."
    // }
    var_dump( $err );
    die();
}


// load that specific object back from the server just to prove it's been edited:
sleep( 1 ); // allow mysql -> couchdb propgation
$o = IXF\IXP::retrieve( $o->id );

echo "ID/Short name after:   " . $o->id . '/' . $o->short_name . "\n";

// and restore its previous value:
$o->short_name = $old_short_name;
$o->save();
sleep( 1 ); // allow mysql -> couchdb propgation
$o = IXF\IXP::retrieve( $o->id );
echo "ID/Short name restore: " . $o->id . '/' . $o->short_name . "\n";




// create a new IXP

$newObjIdArray = IXF\IXP::create( [

    "full_name" => "Test IXP",
    "short_name" => "TIXP"

] );

if( is_array( $newObjIdArray ) && !isset( $newObjIdArray['error'] ) )
    echo "New object created with ID: " . $newObjIdArray['id'] . "\n";
else {
    echo "FAILURE!!!\n\n";
    var_dump( $newObjIdArray );
    die();
}

// retrieve it
sleep( 1 ); // allow mysql -> couchdb propgation
$o = IXF\IXP::retrieve( $newObjIdArray['id'] );

// and delete it
// delete returns true on success and clears the object
$result = $o->delete();

if( $result=== true )
    echo "Object deleted\n";
else
{
    // on failure, you get an array of the form:
    // array(1) {
    //  ["error"]=>
    //  string(49) "DoesNotExist: Ixps matching query does not exist."
    //}
    var_dump( $result );
    die();
}



// and further proof
$o = IXF\IXP::retrieve( $newObjIdArray['id'] );
if( isset( $o->error ) )
    echo "Object not found.\n";
