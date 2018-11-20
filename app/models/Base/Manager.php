<?php

namespace App\Models;

use Dibi\Connection;
use SplFileInfo;

/**
 * Description of Manager
 *
 * @author rendix2
 * @package App\Models
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
     * 
     */
    public function __destruct()
    {
        if ($this->dibi && $this->dibi->isConnected()) {
            $this->dibi->disconnect();
        }
                
        $this->dibi = null;
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
        return mb_substr(md5(uniqid(mt_rand(), true)), 0, 15); // php.vrana.cz
    }
}
