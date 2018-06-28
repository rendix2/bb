<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\BansManager;
use App\Controls\GridFilter;

/**
 * Description of BanPresenter
 *
 * @author rendi
 */
class BanPresenter extends Base\AdminPresenter
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
     *
     */
    public function startup()
    {
        parent::startup();
        
        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('ban_id', 'ban_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('ban_user_name', 'ban_user_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('ban_email', 'ban_email', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('ban_ip', 'ban_ip', GridFilter::TEXT_LIKE);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
    }

    /**
     * @return BootstrapForm|mixed
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->getAdminTranslator());
        
        $form->addText('ban_user_name', 'User name:');
        $form->addText('ban_email', 'User mail:');
        $form->addText('ban_ip', 'User IP:');
        
        $form = $this->addSubmitB($form);
        
        return $form;
    }
}
