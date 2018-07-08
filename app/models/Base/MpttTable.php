<?php

namespace App\Models;

/**
 * Description of MpttTable
 *
 * @author rendi
 */
interface MpttTable {
    
    public function getTable();
    
    public function getPrimaryKey();
    
    public function getTitle();
    
    public function getLeft();
    
    public function getRight();
    
    public function getParent();
    
}
