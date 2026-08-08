<?php

namespace App\AdminModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

class SmileysPresenter extends Presenter
{
    public function __construct(
        private readonly Translator $translator,
    )
    {
        parent::__construct();
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        return $form;
    }
    
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->translator);

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_smileys']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',   'text' => 'menu_index'],
            1 => ['link' => 'Smileys:default', 'text' => 'menu_smileys'],
            2 => ['link' => 'Smileys:edit',    'text' => 'menu_smiley'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
