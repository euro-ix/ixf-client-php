<?php

namespace IXF;

class Object implements \ArrayAccess
{
    /**
     * @var array Attributes that should not be sent to the API because they're
     *    not updatable (e.g. API key, ID).
     */
    public static $permanentAttributes;

    public static function init()
    {
        self::$permanentAttributes = new Util\Set(array('id'));
    }

    protected $_values;
    protected $_unsavedValues;
    protected $_transientValues;
    protected $_retrieveOptions;

    public function __construct($id=null)
    {
        $this->_values = array();
        $this->_unsavedValues = new Util\Set();
        $this->_transientValues = new Util\Set();

        $this->_retrieveOptions = array();
        if (is_array($id)) {
            foreach ($id as $key => $value) {
                if ($key != 'id')
                $this->_retrieveOptions[$key] = $value;
            }
            $id = $id['id'];
        }

        if ($id)
        $this->id = $id;
    }

    // Standard accessor magic methods
    public function __set($k, $v)
    {
        // IXF-FIXME
        if ($v === "") {
                throw new \InvalidArgumentException(
                'You cannot set \''.$k.'\'to an empty string. '
                .'We interpret empty strings as NULL in requests. '
                .'You may set obj->'.$k.' = NULL to delete the property'
            );
        }

        $this->_values[$k] = $v;

        if (!self::$permanentAttributes->includes($k))
            $this->_unsavedValues->add($k);
    }

    public function __isset($k)
    {
        return isset($this->_values[$k]);
    }

    public function __unset($k)
    {
        unset($this->_values[$k]);
        $this->_transientValues->add($k);
        $this->_unsavedValues->discard($k);
    }

    public function __get($k)
    {
        if (array_key_exists($k, $this->_values)) {
            return $this->_values[$k];
        } elseif ($this->_transientValues->includes($k)) {
            $class = get_class($this);
            $attrs = join(', ', array_keys($this->_values));
            // IXF-FIXME
            $message = "IXF Notice: Undefined property of $class instance: $k. "
            . "HINT: The $k attribute was set in the past, however. "
            . "It was then wiped when refreshing the object "
            . "with the result returned by IXF's API, "
            . "probably as a result of a save(). The attributes currently "
            . "available on this object are: $attrs";
            error_log($message);

            return null;
        } else {
            $class = get_class($this);
            error_log("IXF Notice: Undefined property of $class instance: $k");

            return null;
        }
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
        {
            if( self::$permanentAttributes->includes( $k ) )
                continue;
            unset( $this->$k );
        }

        foreach( $values as $k => $v )
        {
            if( self::$permanentAttributes->includes( $k ) )
                continue;

            $this->_values[$k] = Util::convertToIxfObject( $v );

            $this->_transientValues->discard($k);
            $this->_unsavedValues->discard($k);
        }
    }

    /**
    * @return array A recursive mapping of attributes to values for this object,
    *    including the proper value for deleted attributes.
    */
    public function serializeParameters()
    {
        $params = array();
        if ($this->_unsavedValues) {
            foreach ($this->_unsavedValues->toArray() as $k) {
                $v = $this->$k;
                if ($v === NULL) {
                    $v = '';
                }
                $params[$k] = $v;
            }
        }

        return $params;
    }

    // Pretend to have late static bindings, even in PHP 5.2
    protected function _lsb($method)
    {
        $class = get_class($this);
        $args = array_slice(func_get_args(), 1);

        return call_user_func_array(array($class, $method), $args);
    }

    protected static function _scopedLsb($class, $method)
    {
        $args = array_slice(func_get_args(), 2);

        return call_user_func_array(array($class, $method), $args);
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
