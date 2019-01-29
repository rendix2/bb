<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Models\SmileysManager;
use App\Controls\BootstrapForm;

/**
 * Description of SmileysPresenter
 *
 * @author rendix2
 * @method SmileysManager getManager()
 * @package App\AdminModule\Presenters
 */
class SmileysPresenter extends AdminPresenter
{
    /**
     * SmileysPresenter constructor.
     *
     * @param SmileysManager $manager
     */
    public function __construct(SmileysManager $manager)
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
            1 => ['text' => 'menu_smileys']
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
            1 => ['link' => 'Smileys:default', 'text' => 'menu_smileys'],
            2 => ['link' => 'Smileys:edit',    'text' => 'menu_smiley'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
