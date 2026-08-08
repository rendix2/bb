<?php

namespace App\Controls;

use Nette\Application\UI\Control;
use Nette\Localization\Translator;

/**
 * Description of BreadCrumbControl
 *
 * @author rendix2
 * @package App\Controls
 */
class BreadCrumbControl extends Control
{
    public function __construct(
        private readonly array $breadCrumb,
        private readonly Translator $translator
    )
    {
    }

    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->getTemplate()->setFile(__DIR__ . $sep . 'templates' . $sep . 'breadCrumb' . $sep . 'breadCrumb.latte');
        $template->setTranslator($this->translator);
        
        $template->breadCrumb = $this->breadCrumb;
        
        $template->render();
    }
}
