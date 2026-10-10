<?php

class Language
{
    /** @var array */
    private static $catalog = array();

    /**
     * @param bool  $active
     * @param int|false $id_shop
     * @param bool  $ids_only
     *
     * @return array
     */
    public static function getLanguages($active = true, $id_shop = false, $ids_only = false)
    {
        $result = array();
        foreach (self::$catalog as $row) {
            if ($active && empty($row['active'])) {
                continue;
            }
            if ($id_shop !== false && (int) $row['id_shop'] !== (int) $id_shop) {
                continue;
            }
            if ($ids_only) {
                $result[] = (int) $row['id_lang'];
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    public static function resetCatalog(array $rows)
    {
        self::$catalog = $rows;
    }

    public static function clearCatalog()
    {
        self::$catalog = array();
    }
}
