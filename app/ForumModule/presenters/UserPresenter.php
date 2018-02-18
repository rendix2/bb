<?php

namespace App\ForumModule\Presenters;

use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Models\LanguagesManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of UserProfilePresenter
 *
 * @author rendi
 * @method UsersManager getManager()
 */
class UserPresenter extends Base\ForumPresenter
{

    /**
     * @var LanguagesManager $languageManager
     */
    private $languageManager;
    
    private $rankManager;
    
    private $wwwDir;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager     $manager
     * @param LanguagesManager $languageManager
     */
    public function __construct(UsersManager $manager, LanguagesManager $languageManager)
    {
        parent::__construct($manager);

        $this->languageManager = $languageManager;
    }
    
    public function injectRankManager(\App\Models\RanksManager $rankManager){
        $this->rankManager = $rankManager;
    }
    
    public function injectWwwDir(\App\Controls\WwwDir $wwwDir){
        $this->wwwDir = $wwwDir;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserFormSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->getUser();
        $move = $this->getManager()->moveAvatar($values->user_avatar, $this->getUser()->getId(), $this->wwwDir->wwwDir);

        if ( $move !== UsersManager::NOT_UPLOADED ){
            $values->user_avatar = $move;
        }
        else{
            unset($values->user_avatar);
        }

        if ($user->isLoggedIn()) {
            $result = $this->getManager()->update($user->getId(), $values);
        } else {
            $result = $this->getManager()->add($values);
        }

        if ($result) {
            $this->flashMessage('User saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('User:edit');
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserOnValidate(Form $form, ArrayHash $values)
    {

    }

    /**
     *
     */
    public function actionLogout()
    {
        $this->getUser()->logout();

        $this->flashMessage('Successfuly logged out. ', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Index:default');
    }
    
    public function actionActivate($user_id, $key){
        // after register
        if ( !$this->getUser()->isLoggedIn() ){
            $can = $this->getManager()->canBeActivated($user_id, $key);
            
            if ( $can ){
                $this->getManager()->update($user_id, ArrayHash::from(['user_active' => 1, 'user_activation_key' => null]));
                $this->flashMessage('You have been activated.', self::FLASH_MESSAGE_SUCCESS);
                $this->redirect('Login:default');
            }else{
                $this->flashMessage('You cannot be activated.', self::FLASH_MESSAGE_DANGER);
            }
        }
        else{
            $this->flashMessage('You are logged in!', self::FLASH_MESSAGE_DANGER);
        }        
    }
    
    protected function createComponentResetPasswordForm(){
        $form = $this->getBootStrapForm();
        $form->addEmail('user_email', 'User email:');
        $form->addSubmit('send', 'Reset');
        $form->onSuccess[] = [$this, 'resetPasswordFormSuccess'];
        
        return $form;
    }
    
    public function resetPasswordFormSuccess(Form $form, ArrayHash $values){
        $found_mail = $this->getManager()->getByEmail($values->user_email);
        
        if ( $found_mail ){
            // send mail!
            
            $this->flashMessage('Email was sent.', self::FLASH_MESSAGE_SUCCESS);            
        }
        else{
            $this->flashMessage('User mail was not found!', self::FLASH_MESSAGE_DANGER);
        }
    }

    public function renderLostPassword(){
        // give mail and send on that mail mail with info how to change it
        // give link to reset this action if owner of account dont ask to reset pass
    }
    
    public function actionResetLostPassword(){
        // case when you do not send request to reset password
    }
    
    public function actionChangeLostPassword(){
       // set new password 
    }

    /**
     *
     */
    public function renderEdit()
    {
        $user = $this->getUser();

        if ($user->isLoggedIn()) {
            $userData = $this->getManager()->getById($this->getUser()->getId());
        }

        $this['editUserForm']->setDefaults($userData);

        $this->template->item = $userData;
    }

    /**
     * @param int $user_id
     */
    public function renderPosts($user_id)
    {
        if ( !is_numeric($user_id) ){
            $this->error('Parameter is not numeric.');
        }
        
        $user = $this->getManager()->getById($user_id);
        
        if (!$user){
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }
        
        $posts = $this->getManager()->getPosts($user_id);
        
        if ( !$posts ){
            $this->flashMessage('User have no posts', self::FLASH_MESSAGE_WARNING);
        }
                
        $this->template->posts = $posts;
    }

    /**
     * @param int $user_id
     */
    public function renderProfile($user_id)
    {
        if ( !is_numeric($user_id) ){
            $this->error('Parameter is not numeric');            
        }
        
        $userData = $this->getManager()->getById($user_id);

        if (!$userData) {
            $this->error('User not found.');
        }
        
        $ranks    = $this->rankManager->getAllCached();
        $rankUser = null;
        
        foreach ( $ranks as $rank){
            if ( $userData->user_post_count >= $rank->rank_from && $userData->user_post_count <= $rank->rank_to ){
                 $rankUser = $rank; 
                break;
            }
        }
        
        $this->template->userData = $userData;
        $this->template->rank     = $rankUser;
    }

    /**
     * @param int $user_id
     */
    public function renderThanks($user_id)
    {
        if ( !is_numeric($user_id) ){
            $this->error('Parameter is not numeric.');
        }
               
        $user = $this->getManager()->getById($user_id);
        
        if (!$user){
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }        
        
        $thanks = $this->getManager()->getThanks($user_id);
        
        if (!$thanks){
            $this->flashMessage('User have no thanks.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->thanks = $thanks;
    }

    /**
     * @param int $user_id
     */
    public function renderTopics($user_id)
    {
        if ( !is_numeric($user_id) ){
            $this->error('Parameter is not numeric.');
        }
               
        $user = $this->getManager()->getById($user_id);
        
        if (!$user){
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }        
        
        $topics = $this->getManager()->getTopics($user_id);
        
        if ( !$topics ){
            $this->flashMessage('User have no topics.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->topics = $topics;
    }

    /**
     * @return ChangePasswordControl
     */
    protected function createComponentChangePasswordControl()
    {
        return new ChangePasswordControl($this->getManager(), $this->getForumTranslator(), $this->getUser());
    }

    /**
     * @return DeleteAvatarControl
     */
    protected function createComponentDeleteAvatar()
    {
        return new DeleteAvatarControl($this->getManager(), $this->wwwDir, $this->getUser(), $this->getForumTranslator());
    }

    /**
     * @return \App\Controls\BootstrapForm
     */
    protected function createComponentEditUserForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getForumTranslator());
        $form->addText('user_name', 'User name:');
        $form->addSelect('user_lang_id', 'User language:', $this->languageManager->getAllPairsCached('lang_name'));
        $form->addUpload('user_avatar', 'User avatar:');
        $form->addSubmit('send', 'Send');

        $form->onSuccess[] = [
            $this,
            'editUserFormSuccess'
        ];
        $form->onValidate[] = [
            $this,
            'editUserOnValidate'
        ];

        return $form;
    }
}
