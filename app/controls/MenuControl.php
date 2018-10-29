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
    /**
     * @var array $leftMenu
     */
    private $leftMenu;

    /**
     * @var array $rightMenu
     */
    private $rightMenu;

    /**
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * MenuControl constructor.
     *
     * @param ITranslator $translator
     * @param array $leftMenu
     * @param array $rightMenu
     */
    public function __construct(ITranslator $translator, array $leftMenu = [], array $rightMenu = [])
    {
        parent::__construct();
        
        $this->translator = $translator;
        $this->leftMenu   = $leftMenu;
        $this->rightMenu  = $rightMenu;
    }
    
    public function __destruct()
    {
        $this->translator = null;
        $this->leftMenu   = null;
        $this->rightMenu  = null;
    }

    public function render()
    {
        $template = $this->template;
        $sep      = DIRECTORY_SEPARATOR;
                
        $template->setFile(__DIR__ . $sep . 'templates' . $sep . 'menu' . $sep . 'menu.latte');
        $template->setTranslator($this->translator);

        $template->leftMenu  = $this->leftMenu;
        $template->rightMenu = $this->rightMenu;

        $template->render();
    }
}
