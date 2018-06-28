<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Controls\PaginatorControl;
use App\Controls\WwwDir;
use App\Models\LanguagesManager;
use App\Models\MailsManager;
use App\Models\ModeratorsManager;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
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
     * @inject
     */
    public $languageManager;

    /**
     * @var RanksManager $rankManager
     * @inject
     */
    public $rankManager;

    /**
     * @var WwwDir $wwwDir
     * @inject
     */
    public $wwwDir;

    /**
     * @var TopicWatchManager $topicWatchManager
     * @inject
     */
    public $topicWatchManager;

    /**
     * @var TopicsManager $topicManager
     * @inject
     */
    public $topicManager;

    /**
     * @var PostsManager $postManager
     * @inject
     */
    public $postManager;

    /**
     * @var ThanksManager $thanksManager
     * @inject
     */
    public $thanksManager;
    
    /**
     * @var \App\Controls\Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     * @var ModeratorsManager $moderatorsManager
     * @inject
     */
     public $moderatorsManager;
    
    /**
     *
     * @var IMailer $mailer
     */
    private $mailer;
    
    /**
     * @var MailsManager $mailManager
     * @inject
     */
    public $mailManager;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     * @param IMailer      $mailer
     */
    public function __construct(UsersManager $manager, IMailer $mailer)
    {
        parent::__construct($manager);
        
        $this->mailer = $mailer;
    }
    
    /**
     * @param int    $user_id
     * @param string $key
     */
    public function actionActivate($user_id, $key)
    {
        // after register
        if (!$this->getUser()
            ->isLoggedIn()) {
            $can = $this->getManager()
                ->canBeActivated($user_id, $key);

            if ($can) {
                $this->getManager()
                    ->update(
                        $user_id,
                        ArrayHash::from(
                            [
                                'user_active'         => 1,
                                'user_activation_key' => null
                            ]
                        )
                    );
                $this->flashMessage('You have been activated.', self::FLASH_MESSAGE_SUCCESS);
                $this->redirect('Login:default');
            } else {
                $this->flashMessage('You cannot be activated.', self::FLASH_MESSAGE_DANGER);
            }
        } else {
            $this->flashMessage('You are logged in!', self::FLASH_MESSAGE_DANGER);
        }
    }

    /**
     *
     */
    public function actionChangeLostPassword()
    {
        // set new password
    }

    /**
     *
     */
    public function actionLogout()
    {
        $this->sessionManager->deleteBySessionId($this->getSession()->getId());
        $this->getUser()->logout();

        $this->flashMessage('Successfully logged out. ', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Index:default');
    }

    /**
     *
     */
    public function actionResetLostPassword()
    {
        // case when you do not send request to reset password
    }

    /**
     *
     */
    public function renderEdit()
    {
        $user     = $this->getUser();
        $userData = [];

        if ($user->isLoggedIn()) {
            $userData = $this->getManager()->getById($user->getId());
        }

        $this['editUserForm']->setDefaults($userData);

        $this->template->item = $userData;
    }

    /**
     *
     */
    public function renderLostPassword()
    {
        // give mail and send on that mail mail with info how to change it
        // give link to reset this action if owner of account don't ask to reset pass
    }
    
    /**
     * @param int $user_id
     * @param int $page
     */
    public function renderPosts($user_id, $page = 1)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->getManager()->getById($user_id);

        if (!$user) {
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }

        $posts = $this->getManager()->getPosts($user_id);
        $pag   = new PaginatorControl($posts, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no posts', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $posts->fetchAll();
    }

    /**
     * @param int $user_id
     */
    public function renderProfile($user_id)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric');
        }

        $userData = $this->getManager()->getById($user_id);

        if (!$userData) {
            $this->error('User not found.');
        }

        $ranks    = $this->rankManager->getAllCached();
        $rankUser = null;

        foreach ($ranks as $rank) {
            if ($userData->user_post_count >= $rank->rank_from && $userData->user_post_count <= $rank->rank_to) {
                $rankUser = $rank;
                break;
            }
        }
        
        $this->template->moderatorForums = $this->moderatorsManager->getAllJoinedByLeft($user_id);
        $this->template->thankCount      = $this->thanksManager->getCountCached();
        $this->template->topicCount      = $this->topicManager->getCountCached();
        $this->template->postCount       = $this->postManager->getCountCached();
        $this->template->watchTotalCount = $this->topicWatchManager->getCount();
        $this->template->userData        = $userData;
        $this->template->rank            = $rankUser;
        $this->template->roles           = \App\Authorizator::ROLES;
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function renderThanks($user_id, $page = 1)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->getManager()->getById($user_id);

        if (!$user) {
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }

        $thanks = $this->getManager()->getThanks($user_id);
        $pag    = new PaginatorControl($thanks, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no thanks.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->thanks = $thanks->fetchAll();
    }

    /**
     * @param int $user_id user_id
     * @param int $page    page
     */
    public function renderTopics($user_id, $page = 1)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->getManager()->getById($user_id);

        if (!$user) {
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }

        $topics = $this->getManager()->getTopics($user_id);
        $pag    = new PaginatorControl($topics, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no topics.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->topics = $topics->fetchAll();
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function renderWatches($user_id, $page = 1)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->getManager()->getById($user_id);

        if (!$user) {
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }
        
        $watches = $this->topicWatchManager->getFluentJoinedByRight($user_id);
        
        $pag    = new PaginatorControl($watches, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no awcthes.', self::FLASH_MESSAGE_WARNING);
        }
           
        $this->template->watches = $watches->fetchAll();
    }

    /**
     * @param $page
     */
    public function renderList($page)
    {
        $users = $this->getManager()->getAllFluent();
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 1;
        $this->template->users = $users;
    }

    /**
     * @param $page
     */
    public function renderModeratorList($page)
    {
        $this->template->setFile(__DIR__.'/../templates/User/list.latte');
        
                $users = $this->getManager()
                ->getAllFluent()
                ->where('[user_role_id] = %i', 3);
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 3;
        $this->template->users = $users;
    }

    /**
     * @param $page
     */
    public function renderAdminList($page)
    {
                $this->template->setFile(__DIR__.'/../templates/User/list.latte');
        
        $users = $this->getManager()
                ->getAllFluent()
                ->where('[user_role_id] = %i', 5);
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 5;
        $this->template->users = $users;
    }

    public function renderRegister()
    {
        // todo
    }
    
    public function renderSendMailToAdmin()
    {
        // TODO
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentSendMailToAdmin()
    {
        $form = $this->getBootstrapForm();
        
        $form->addText('mail_subject', 'Mail subject:')->setRequired('Subject is required.');
        $form->addTextArea('mail_text', 'Mail text:', null, 10)->setRequired('Text is required.');
        
        $form->addSubmit('send', 'Send mail');
        $form->onSuccess[] = [$this, 'sendMailToAdminSuccess'];
        
        return $form;
    }

    /**
     * @return ChangePasswordControl
     */
    protected function createComponentChangePasswordControl()
    {
        return new ChangePasswordControl(
            $this->getManager(),
            $this->getForumTranslator(),
            $this->getUser()
        );
    }

    /**
     * @return DeleteAvatarControl
     */
    protected function createComponentDeleteAvatar()
    {
        return new DeleteAvatarControl(
            $this->getManager(),
            $this->wwwDir,
            $this->getUser(),
            $this->getForumTranslator()
        );
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditUserForm()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->getForumTranslator());
        $form->addText(
            'user_name',
            'User name:'
        );
        $form->addSelect(
            'user_lang_id',
            'User language:',
            $this->languageManager->getAllPairsCached('lang_name')
        );
        $form->addUpload(
            'user_avatar',
            'User avatar:'
        )->setAttribute('title', 'Max width: '.$this->avatar->getMaxWidth().'px, max height: '.$this->avatar->getMaxHeight().'px')
                ->setRequired(false)
                ->addRule(Form::IMAGE, 'user_avatar_file_rule');
        $form->addTextArea('user_signature', 'User signature:');
        $form->addSubmit(
            'send',
            'Send'
        );

        $form->onSuccess[]  = [
            $this,
            'editUserFormSuccess'
        ];
        $form->onValidate[] = [
            $this,
            'editUserOnValidate'
        ];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentResetPasswordForm()
    {
        $form = $this->getBootstrapForm();
        $form->addEmail(
            'user_email',
            'User email:'
        );
        $form->addSubmit(
            'send',
            'Reset'
        );
        $form->onSuccess[] = [
            $this,
            'resetPasswordFormSuccess'
        ];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserFormSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->getUser();
        
        try {
            $move = $this->getManager()->moveAvatar($values->user_avatar, $user->getId(), $this->wwwDir->wwwDir);
            
            if ($move !== UsersManager::NOT_UPLOADED) {
                $values->user_avatar = $move;
            } else {
                unset($values->user_avatar);
            }
        } catch (\Nette\InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage());
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
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function resetPasswordFormSuccess(Form $form, ArrayHash $values)
    {
        $found_mail = $this->getManager()->getByEmail($values->user_email);

        if ($found_mail) {
            // send mail!

            $this->flashMessage('Email was sent.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('User mail was not found!', self::FLASH_MESSAGE_DANGER);
        }
    }
    
    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function sendMailToAdminSuccess(Form $form, ArrayHash $values)
    {
        $admins = $this->getManager()->getAllFluent()->where('[user_role_id] = %i', 5)->fetchAll();
        
        $adminsMails = [];
        
        foreach ($admins as $admin) {
            $adminsMails[] = $admin->user_email;
        }
                
        $mailer = new \App\Controls\BBMailer($this->mailer, $this->mailManager);
        $mailer->addRecepients($adminsMails);
        $mailer->setSubject($values->mail_subject);
        $mailer->setText($values->mail_text);
        $res = $mailer->send();
        
        if ($res) {
            $this->flashMessage('Mail sent.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Mail was not sent.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }
}
