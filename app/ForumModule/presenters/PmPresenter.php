<?php

namespace App\ForumModule\Presenters;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends Base\ForumPresenter
{
    public function __construct(\App\Models\PMManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function startup() {
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

    protected function createComponentEditForm()
    {
        $form = self::createBootstrapForm();
        
        $form->addHidden('pm_to_user_id');
        $form->addHidden('pm_from_user_id');
        $form->addText('pm_subject', 'Subject:');
        $form->addText('pm_user_name', 'User name:');
        $form->addTextArea('pm_text', 'Text:');
               
        return $form;
    }

}
