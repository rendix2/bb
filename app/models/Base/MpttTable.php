<?php

namespace App\Models;

/**
 * Description of MpttTable
 *
 * @author rendix2
 */
interface MpttTable {
    
    public function getTable();
    
    public function getPrimaryKey();
    
    public function getTitle();
    
    public function getLeft();
    
    public function getRight();
    
    public function getParent();
    
}
