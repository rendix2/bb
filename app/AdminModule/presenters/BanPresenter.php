<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Models\BansManager;
use App\Controls\GridFilter;
use App\Controls\BreadCrumbControl;

/**
 * Description of BanPresenter
 *
 * @author rendix2
 * @method BansManager getManager()
 */
class BanPresenter extends AdminPresenter
{
    /**
     * BanPresenter constructor.
     *
     * @param BansManager $manager
     */
    public function __construct(BansManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return BootstrapForm|mixed
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        
        $form->addText('ban_user_name', 'User name:');
        $form->addText('ban_email', 'User mail:');
        $form->addText('ban_ip', 'User IP:');
        
        return $this->addSubmitB($form);
    }
    
    /**
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getAdminTranslator());
            
        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('ban_id', 'ban_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('ban_user_name', 'ban_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_email', 'ban_email', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_ip', 'ban_ip', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_bans']
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
            1 => ['link' => 'Ban:default', 'text' => 'menu_bans'],
            2 => ['link' => 'Ban:default', 'text' => 'menu_ban'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getAdminTranslator());
    }
}
