<?php

namespace App\Controls;

use App\Models\PmManager;
use Nette\Application\UI\Control;

/**
 * Description of PmControl
 *
 * @author rendix2
 * @package App\Controls
 */
class PmControl extends Control
{
    /**
     *
     * @var PMManager $pmManager
     */
    private $pmManager;

    /**
     * PmControl constructor.
     *
     * @param PMManager $pmManager
     */
    public function __construct(PMManager $pmManager)
    {
        parent::__construct();
        
        $this->pmManager = $pmManager;
    }
    
    public function render()
    {
    }
}
