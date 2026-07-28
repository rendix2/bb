<?php

namespace App\Controls;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Description of BreadCrumbControl
 *
 * @author rendix2
 * @package App\Controls
 */
class BreadCrumbControl extends Control
{
    /**
     * @var array $breadCrumb
     */
    private array $breadCrumb;
    
    /**
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    /**
     * BreadCrumbControl constructor.
     *
     * @param array       $breadCrumb
     * @param ITranslator $translator
     */
    public function __construct(array $breadCrumb, ITranslator $translator)
    {
        parent::__construct();
        
        $this->breadCrumb = $breadCrumb;
        $this->translator = $translator;
    }

    /**
     * BreadCrumbControl render.
     *
     * render breadcrumb
     */
    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->getTemplate()->setFile(__DIR__ . $sep . 'templates' . $sep . 'breadCrumb' . $sep . 'breadCrumb.latte');
        $template->setTranslator($this->translator);
        
        $template->breadCrumb = $this->breadCrumb;
        
        $template->render();
    }
}
