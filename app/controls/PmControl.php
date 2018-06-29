<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controls;

use App\Models\PMManager;

/**
 * Description of PmControl
 *
 * @author rendi
 */
class PmControl extends \Nette\Application\UI\Control
{
    /**
     *
     * @var PMManager $pmManager
     * @inject
     */
    public $pmManager;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function render()
    {
    }
}
