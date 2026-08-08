<?php

namespace App\Controls;

use Nette\Application\Attributes\Deprecated;
use Nette\Application\UI\Control;
use Nette\Localization\Translator;

/**
 * Description of MenuControl
 *
 * @author rendix2
 * @package App\Controls
 */
#[Deprecated]
class MenuControl extends Control
{

    public function __construct(
        private readonly Translator $translator,
        private readonly array       $leftMenu = [],
        private readonly array       $rightMenu = []
    ) {
        parent::__construct();

    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $sep      = DIRECTORY_SEPARATOR;
                
        $template->setFile(__DIR__ . $sep . 'templates' . $sep . 'menu' . $sep . 'menu.latte');
        $template->setTranslator($this->translator);

        $template->leftMenu  = $this->leftMenu;
        $template->rightMenu = $this->rightMenu;

        $template->render();
    }
}
