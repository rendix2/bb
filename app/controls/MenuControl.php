<?php

namespace App\Controls;

use Nette\Application\UI\Control;

/**
 * Description of MenuCotrol
 *
 * @author rendi
 */
class MenuControl extends Control
{
    public function __construct() {
        parent::__construct();
    }
    
    public function render()
    {        
        $template = $this->template;
        
        $template->setFile(__DIR__ . '/templates/menu/menu.latte');
    }
}
