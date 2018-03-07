<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Dibi\Connection;
use SplFileInfo;

/**
 * Description of Manager
 *
 * @author rendi
 */
abstract class Manager extends Tables
{

    /**
     *
     * @var Connection $dibi dibi
     */
    protected $dibi;

    /**
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi)
    {
        $this->dibi = $dibi;
    }

    /**
     * @return Connection
     */
    public function getDibi()
    {
        return $this->dibi;
    }

    /**
     * returns extension of file
     *
     * @param string $fileName file name
     *
     * @return string
     * @api
     */
    public static function getFileExtension($fileName)
    {
        $file = new SplFileInfo($fileName);

        return $file->getExtension();
    }

    /**
     * this method returns random string
     *
     * @return string
     * @see    https://php.vrana.cz/trvale-prihlaseni.php php vrana
     */
    public static function getRandomString()
    {
        return mb_substr(
            md5(
                uniqid(
                    mt_rand(),
                    true
                )
            ),
            0,
            15
        ); // php.vrana.cz
    }
}
