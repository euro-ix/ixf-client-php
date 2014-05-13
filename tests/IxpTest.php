<?php

class IxpTest extends PHPUnit_Framework_TestCase
{
    const IXP_TEST_OBJECT_ID = 42;

    public function testGetAll()
    {
        $ixps = IXF\IXP::all();
        $this->assertGreaterThan( 400, count( $ixps ) );
        $this->assertTrue( is_array( $ixps ) );
        $this->assertInstanceOf( 'IXF\Object', array_pop( $ixps ) );
        $this->assertInstanceOf( 'IXF\Object', array_shift( $ixps ) );
    }

    public function testAllIterate()
    {
        $ixps = IXF\IXP::all();

        $last = IXF\IXP::all( [ 'limit' => 2 ] );
        $this->assertEquals( 2, count( $last ) );

        // iterate over records 2 at a time
        foreach( range( 1, count( $ixps ) - 2 ) as $i )
        {
            $objs = IXF\IXP::all( [ 'limit' => 2, 'skip' => $i ] );
            $this->assertEquals( 2, count( $objs ) );
            $this->assertGreaterThan( $objs[0]->id, $objs[1]->id );
            $this->assertEquals( $last[1], $objs[0] );
            $last = $objs;
        }
    }

    /**
     * @expectedException IXF\Error\NotFound
     */
    public function testGetNotFound()
    {
        IXF\IXP::retrieve( 420000 );
    }

    public function testGet()
    {
        $this->assertEquals( self::IXP_TEST_OBJECT_ID, IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID )->id );
    }

    public function testCreate()
    {
        $newObjId = IXF\IXP::create( [
            "full_name" => "PHP Clinet API - phpUnit Test Create",
            "short_name" => "IXF-PHP-Client-phpUnit"
        ] );

        $this->assertTrue( is_numeric( $newObjId ) );
        $this->assertGreaterThan( 0, $newObjId );

        sleep( 1 );
        $obj = IXF\IXP::retrieve( $newObjId );
        $this->assertEquals( $newObjId, $obj->id );
        $this->assertEquals( "PHP Clinet API - phpUnit Test Create", $obj->full_name );
        $obj->delete();
    }


    public function testSave()
    {
        $state = IXF\Util::randomString( 20 );

        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $oldstate = $obj->state;
        $obj->state = $state;
        $obj->save();
        sleep( 1 );
        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $this->assertEquals( $state, $obj->state );

        $obj->state = $oldstate;
        $obj->save();
        sleep( 1 );
        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $this->assertEquals( $oldstate, $obj->state );
    }

    public function testDelete()
    {
        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $this->assertEquals( self::IXP_TEST_OBJECT_ID, $obj->id );
        $this->assertTrue( is_string($obj->state) && strlen( $obj->state ) );

        $this->assertTrue( $obj->delete() );
        sleep(1);

        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $this->assertEquals( 'deleted', $obj->state );
        return;
        $obj->state = 'active';
        $obj->save();
        sleep(1);

        $obj = IXF\IXP::retrieve( self::IXP_TEST_OBJECT_ID );
        $this->assertEquals( 'active', $obj->state );
    }
}
