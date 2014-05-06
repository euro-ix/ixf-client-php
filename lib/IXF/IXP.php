<?php

namespace IXF;

class IXP extends ApiResource
{

    public static function constructFrom($values)
    {
        $class = get_class();

        return self::scopedConstructFrom($class, $values);
    }

    /**
     * @param array|null $params
     *
     * @return array An array of ixps.
     */
    public static function all($params=null)
    {
        $class = get_class();
        return self::scopedAll($class, $params, 'Ixps');
    }

    public static function retrieve($id, $resource = 'Ixps')
    {
        $class = get_class();
        return self::scopedRetrieve($class, $id, $resource);
    }

    /**
     * @param array|null $params
     *
     * @return IXP The deleted ixp.
     */
    public function delete($params=null)
    {
        $class = get_class();
        return self::scopedDelete($class, $params, 'Ixps');
    }

    /**
     * @return IXP The saved ixp.
     */
    public function save()
    {
        $class = get_class();
        return self::scopedSave($class, 'Ixps');
    }


    /**
     * @param array|null $params
     * @param string|null $apiKey
     *
     * @return IXP The created customer.
     */
    public static function create($params=null, $resource = 'Ixps')
    {
        $class = get_class();
        return self::scopedCreate($class, $params, $resource);
    }
}
