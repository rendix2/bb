<?php

namespace App\Controls;

use App\Models\PMManager;
use Nette\Application\UI\Control;

/**
 * Description of PmControl
 *
 * @author rendi
 */
class PmControl extends Control
{
    /**
     *
     * @var PMManager $pmManager
     */
    private $pmManager;
    
    public function __construct(PMManager $pmManager)
    {
        parent::__construct();
        
        $this->pmManager = $pmManager;
    }
    
    public function render()
    {
    }
}
