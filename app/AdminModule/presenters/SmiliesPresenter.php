<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Models\SmilesManager;
use App\Controls\BootstrapForm;

/**
 * Description of SmiliesPresenter
 *
 * @author rendix2
 * @method SmilesManager getManager()
 * @package App\AdminModule\Presenters
 */
class SmiliesPresenter extends AdminPresenter
{
    /**
     * SmiliesPresenter constructor.
     *
     * @param SmilesManager $manager
     */
    public function __construct(SmilesManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        
        return $form;
    }
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);

        return $this->gf;
    }    

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_smilies']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',   'text' => 'menu_index'],
            1 => ['link' => 'Smilies:default', 'text' => 'menu_smilies'],
            2 => ['link' => 'Smilies:edit',    'text' => 'menu_smilie'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
