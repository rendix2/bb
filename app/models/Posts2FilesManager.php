<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of PostFilesManager
 *
 * @author rendix2
 * @package App\Models
 */
class Posts2FilesManager extends MNManager
{

    /**
     * Posts2FilesManager constructor.
     *
     * @param Connection   $dibi
     * @param IStorage     $storage
     * @param PostsManager $left
     * @param FilesManager $right
     */
    public function __construct(Connection $dibi, IStorage $storage, PostsManager $left, FilesManager $right)
    {
        parent::__construct($dibi, $storage, $left, $right);
    }
}
