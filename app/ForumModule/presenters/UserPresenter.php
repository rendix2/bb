<?php

namespace App\ForumModule\Presenters;

/**
 * Description of UserProfilePresenter
 *
 * @author rendi
 * @method \App\Models\UsersManager getManager()
 */
class UserPresenter extends Base\ForumPresenter {
    
    private $languageManager;

    public function __construct(\App\Models\UsersManager $manager, \App\Models\LanguagesManager $languageManager) {
        parent::__construct($manager);
        
        $this->languageManager = $languageManager;
    }
    
    public function actionLogout(){
        $this->getUser()->logout();
               
        $this->flashMessage('Successfuly logged out. ', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Index:default');
    }    
    
    public function renderProfile($user_id){
        $userData = $this->getManager()->getById($user_id);
        
        if ( !$userData ){
            $this->error('User not found.');
        }
        
        $this->template->userData = $userData;
    }
    
    public function renderPosts($user_id){
        
        $this->template->posts = $this->getManager()->getPosts($user_id);
    }
    
    public function renderTopics($user_id){
        $this->template->topics = $this->getManager()->getTopics($user_id);
    }
    
    public function renderThanks($user_id){
        $this->template->thanks = $this->getManager()->getThanks($user_id);              
    }

    public function renderEdit(){
        $user = $this->getUser();
        
        if ( $user->isLoggedIn() ){
            $userData = $this->getManager()->getById($this->getUser()->getId());
        }
        
        $this['editUserForm']->setDefaults($userData);
    }
    
    protected function createComponentEditUserForm(){
        $form = $this->getBootStrapForm();
        
        $form->setTranslator($this->getForumTranslator());
        
        $form->addText('user_name', 'User name:');
        $form->addSelect('user_lang_id', 'User language:', $this->languageManager->getAllForSelect());
        
        $form->addSubmit('send', 'Send');
        
        $form->onSuccess[]  = [$this, 'editUserFormSuccess'];
        $form->onValidate[] = [$this, 'editUserOnValidate'];
        
        return $form;
    }
    
    public function editUserFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values){
        $user = $this->getUser();
        
        if ( $user->isLoggedIn() ){
            $result = $this->getManager()->update($user->getId(), $values);
        }
        else{
            $result = $this->getManager()->add($values);
        }       
        
        if ( $result ){
            $this->flashMessage('User saved.', self::FLASH_MESSAGE_SUCCES);
        }
        else{
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }        
        
        $this->redirect('User:edit');
    }
    
    public function editUserOnValidate(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values){
        
    }    
}