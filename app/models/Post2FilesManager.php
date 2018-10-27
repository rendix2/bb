<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of PostFilesManager
 *
 * @author rendix2
 */
class Posts2FilesManager extends MNManager
{
    
    /**
     * 
     * @param Connection         $dibi
     * @param PostsManager $left
     * @param FilesManager $right
     */
    public function __construct(Connection $dibi, PostsManager $left, FilesManager $right)
    {
        parent::__construct($dibi, $left, $right, $tableName, $leftKey, $rightKey);
    }
}
