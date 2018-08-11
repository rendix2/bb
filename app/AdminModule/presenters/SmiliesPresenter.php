<?php

namespace App\AdminModule\Presenters;

use App\Models\SmiliesManager;
use App\Controls\BreadCrumbControl;

/**
 * Description of SmiliesPresenter
 *
 * @author rendi
 */
class SmiliesPresenter extends Base\AdminPresenter
{
    /**
     * SmiliesPresenter constructor.
     *
     * @param SmiliesManager $manager
     */
    public function __construct(SmiliesManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return mixed
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        
        return $form;
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
        
        return new BreadCrumbControl($breadCrumb, $this->getAdminTranslator());
    }
    
    /**
     * @return BreadCrumbControl
     */    
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Smilies:default', 'text' => 'menu_smilies'],            
            2 => ['link' => 'Smilies:edit',    'text' => 'menu_smilie'],
        ];       
        
        return new BreadCrumbControl($breadCrumb, $this->getAdminTranslator());        
    }
}
