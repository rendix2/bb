<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Nette\Caching\Cache;

/**
 * Description of Manager
 *
 * @author rendi
 */
abstract class Manager extends Tables {

    /**
     * 
     * @var \Dibi\Connection $dibi dibi
     */
    protected $dibi;

    /**
     * 
     * @param \Dibi\Connection $dibi
     */
    public function __construct(\Dibi\Connection $dibi) {
        $this->dibi = $dibi;        
    }

    public function getDibi() {
        return $this->dibi;
    }

}
