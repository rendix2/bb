<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PMManager;
use App\Presenters\crud\CrudPresenter;
use App\Controls\GridFilter;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends CrudPresenter
{    
    /**
     * @var \App\Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * PmPresenter constructor.
     *
     * @param PMManager $manager
     */
    public function __construct(PMManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function startup() {
        parent::startup();
        
        $translator = $this->translatorFactory->forumTranslatorFactory();
        
        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($translator);
            
            $this->gf->addFilter('ban_id', 'ban_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('ban_user_name', 'ban_user_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('ban_email', 'ban_email', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('ban_ip', 'ban_ip', GridFilter::TEXT_LIKE);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }  
        
        $this->template->setTranslator($translator);
        $this->getUser()->setAuthorizator($this->authorizator->getAcl());
    }    

    /**
     * @return BootstrapForm|
     */
    protected function createComponentEditForm()
    {
        $form = self::createBootstrapForm();
        
        $form->addHidden('pm_to_user_id');
        $form->addHidden('pm_from_user_id');
        $form->addText('pm_subject', 'Subject:');
        $form->addText('pm_user_name', 'User name:');
        $form->addTextArea('pm_text', 'Text:');
        
        return $this->addSubmitB($form);
    }
}
