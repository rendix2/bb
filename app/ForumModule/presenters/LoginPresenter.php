<?php

namespace App\ForumModule\Presenters;

use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Forms\UserLoginForm;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends BasePresenter
{
    /**
     * @var string $backlink
     * @persistent
     */
    public $backlink = '';
    
    /**
     *
     * @var UserLoginFormFactory $userLoginForm
     * @inject
     */
    public $userLginForm;
    
    /**
     * 
     * @param mixed $element
     */
    public function checkRequirements($element)
    {       
        $this->getUser()->getStorage()->setNamespace('frontend');
        
        parent::checkRequirements($element);
    }   
    
    /**
     * before render method
     * sets translator
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translatorFactory->forumTranslatorFactory());
    }
    
    /**
     * 
     * @return UserLoginForm
     */
    public function createComponentLoginForm()
    {
        return $this->userLginForm->create(); 
    }
}
