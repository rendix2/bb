<?php

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
