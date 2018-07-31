<?php

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
            
        $this->gf->addFilter('ban_id', 'ban_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('ban_user_name', 'ban_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_email', 'ban_email', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_ip', 'ban_ip', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(null, null, GridFilter::NOTHING);
        
        return $this->gf;
    }
}
