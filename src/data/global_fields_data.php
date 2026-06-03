<?php

class GPAI_USE_DATA_GLOBAL_FIELDS
{
    const INDEX_KEY = 'GPAI_GLOBAL_FIELDS_INDEX';
    const PREFIX = 'GPAI_GLOBAL_FIELDS_';

    public function get($key = null)
    {
        if ($key !== null) {
            return get_option(self::PREFIX . $key, '');
        }
        return $this->getAll();
    }

    public function getAll()
    {
        $index = get_option(self::INDEX_KEY, []);
        $result = [];
        foreach ($index as $field_key) {
            $result[$field_key] = get_option(self::PREFIX . $field_key, '');
        }
        return $result;
    }

    public function setField($key, $value)
    {
        update_option(self::PREFIX . $key, $value);

        $index = get_option(self::INDEX_KEY, []);
        if (!in_array($key, $index)) {
            $index[] = $key;
            update_option(self::INDEX_KEY, $index);
        }
    }

    public function deleteField($key)
    {
        delete_option(self::PREFIX . $key);

        $index = get_option(self::INDEX_KEY, []);
        $index = array_values(array_diff($index, [$key]));
        update_option(self::INDEX_KEY, $index);

        return $index;
    }
}
