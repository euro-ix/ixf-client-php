<?php

namespace IXF;

class ORG extends ApiResource
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
        return self::scopedAll($class, $params, 'ORG');
    }

    public static function retrieve($id, $resource = 'ORG')
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
        return self::scopedDelete($class, $params, 'ORG');
    }

    /**
     * @return IXP The saved ixp.
     */
    public function save()
    {
        $class = get_class();
        return self::scopedSave($class, 'ORG');
    }

    /**
     * @param array|null $params
     */
    public static function create($params=null, $resource = 'ORG')
    {
        $class = get_class();
        return self::scopedCreate($class, $params, $resource);
    }
}
