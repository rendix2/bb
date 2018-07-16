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
        $sep      = DIRECTORY_SEPARATOR;
        
        
        $template->setFile(__DIR__ . $sep . 'templates' . $sep . 'menu' . $sep . 'menu.latte');
    }
}
