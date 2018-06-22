<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controls;

/**
 * Description of PmControl
 *
 * @author rendi
 */
class PmControl extends \Nette\Application\UI\Control
{
    /**
     *
     * @var \App\Models\PMManager $pmManager
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
