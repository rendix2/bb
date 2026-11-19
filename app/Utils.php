<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 13. 11. 2018
 * Time: 10:34
 */

namespace App;

/**
 * Class Utils
 * @author Tomáš Babický tomas.babicky@websta.de
 * @package App
 */
class Utils
{
    /**
     * @param array  $array
     * @param string $column
     * @return array
     */
    public static function arrayObjectColumn(array $array, $column)
    {
        $tmp = [];

        foreach ($array as $row) {
            $tmp[] = $row->{$column};
        }

        return $tmp;
    }
}
