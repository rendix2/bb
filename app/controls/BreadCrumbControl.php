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
    private array $breadCrumb;
    
    private Translator $translator;

    /**
     * BreadCrumbControl constructor.
     *
     * @param array       $breadCrumb
     * @param ITranslator $translator
     */
    public function __construct(array $breadCrumb, Translator $translator)
    {
        $this->breadCrumb = $breadCrumb;
        $this->translator = $translator;
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
