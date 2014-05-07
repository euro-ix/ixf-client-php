<?php

namespace IXF;

class Object implements \ArrayAccess
{
    public static function init()
    {}

    protected $_values;
    protected $_retrieveOptions;

    public function __construct($id=null)
    {
        $this->_values = array();

        if( $id )
            $this->id = $id;
    }

    // Standard accessor magic methods
    public function __set($k, $v)
    {
        $this->_values[$k] = $v;
    }

    public function __isset($k)
    {
        return isset($this->_values[$k]);
    }

    public function __unset($k)
    {
        unset($this->_values[$k]);
    }

    public function __get($k)
    {
        if( array_key_exists( $k, $this->_values ) )
        {
            return $this->_values[$k];
        }
        else
            return null;
    }

    // ArrayAccess methods
    public function offsetSet($k, $v)
    {
        $this->$k = $v;
    }

    public function offsetExists($k)
    {
        return array_key_exists($k, $this->_values);
    }

    public function offsetUnset($k)
    {
        unset($this->$k);
    }
    public function offsetGet($k)
    {
        return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
    }

    public function keys()
    {
        return array_keys($this->_values);
    }

    /**
    * This unfortunately needs to be public to be used in Util.php
    *
    * @param Object $class
    * @param array $values
    *
    * @return Object The object constructed from the given values.
    */
    public static function scopedConstructFrom($class, $values)
    {
        $class = 'IXF\\' . $class;
        if( isset( $values[ 'pk' ] ) )
            $values[ 'id' ] = $values[ 'pk' ];

        $obj = new $class(isset($values['id']) ? $values['id'] : null);
        $obj->refreshFrom($values);
        return $obj;
    }

    /**
    * @param array $values
    *
    * @return Object The object of the same class as $this constructed
    *    from the given values.
    */
    public static function constructFrom($values)
    {
        $class = get_class($this);

        return self::scopedConstructFrom($class, $values);
    }

    /**
    * Refreshes this object using the provided values.
    *
    * @param array $values
    * @param boolean $partial Defaults to false.
    */
    public function refreshFrom( $values )
    {
        $removed = array_diff( array_keys( $this->_values ), array_keys( $values ) );

        foreach( $removed as $k )
            unset( $this->$k );

        foreach( $values as $k => $v )
            $this->_values[$k] = Util::convertToIxfObject( $v );
    }

    /**
    * @return array A recursive mapping of attributes to values for this object,
    *    including the proper value for deleted attributes.
    */
    public function serializeParameters()
    {
        $params = array();

        foreach( $this->_values as $k )
        {
            $v = $this->$k;
            if( $v === NULL )
                $v = '';

            $params[$k] = $v;
        }

        return $params;
    }

    public function __toJSON()
    {
        if (defined('JSON_PRETTY_PRINT'))
            return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
        else
            return json_encode($this->__toArray(true));
    }

    public function __toString()
    {
        return $this->__toJSON();
    }

    public function __toArray($recursive=false)
    {
        if ($recursive)
            return Util::convertIxfObjectToArray($this->_values);
        else
            return $this->_values;
    }
}

Object::init();
