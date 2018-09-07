<?php

namespace App\Controls;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
/**
 * Description of MenuControl
 *
 * @author rendix2
 */
class MenuControl extends Control
{
    private $menu;
    
    private $translator;

    public function __construct(ITranslator $translator, array $menu)
    {
        parent::__construct();
        
        $this->translator = $translator;
        $this->menu       = $menu;
    }
    
    public function render()
    {
        $template = $this->template;
        $sep      = DIRECTORY_SEPARATOR;
                
        $template->setFile(__DIR__ . $sep . 'templates' . $sep . 'menu' . $sep . 'menu.latte');
        
        $template->setTranslator($this->translator);
        $template->menu = $this->menu;
        
        $template->render();
    }
}
