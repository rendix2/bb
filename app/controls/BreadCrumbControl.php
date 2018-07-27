<?php

namespace App\Controls;

use Nette\Localization\ITranslator;
use Nette\Application\UI\Control;

/**
 * Description of BreadCrumbControl
 *
 * @author rendi
 */
class BreadCrumbControl extends Control
{
    /**
     * @var array $breadCrumb
     */
    private $breadCrumb;
    
    /**
     * @var ITranslator $translator
     */
    private $translator;

    /**
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
     * render breadcrumb
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'breadCrumb' . $sep . 'breadCrumb.latte');
        $template->setTranslator($this->translator);
        
        $template->breadCrumb = $this->breadCrumb;
        
        $template->render();
    }
}
