<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Controls\PaginatorControl;
use App\Controls\WwwDir;
use App\Models\LanguagesManager;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
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

    /**
     * @var RanksManager $rankManager
     */
    private $rankManager;

    /**
     * @var WwwDir $wwwDir
     */
    private $wwwDir;

    /**
     * @var TopicWatchManager $topicWatchManager
     */
    private $topicWatchManager;

    /**
     * @var TopicsManager $topicManager
     */
    private $topicManager;

    /**
     * @var PostsManager $postManager
     */
    private $postManager;

    /**
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;
    
    private $avatar;

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
    
    public function injectAvatar(\App\Controls\Avatars $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @param PostsManager $postManager
     */
    public function injectPostManager(PostsManager $postManager)
    {
        $this->postManager = $postManager;
    }

    /**
     * @param RanksManager $rankManager
     */
    public function injectRankManager(RanksManager $rankManager)
    {
        $this->rankManager = $rankManager;
    }

    /**
     * @param ThanksManager $thanksManager
     */
    public function injectThankManager(ThanksManager $thanksManager)
    {
        $this->thanksManager = $thanksManager;
    }

    /**
     * @param TopicsManager $topicManager
     */
    public function injectTopicManager(TopicsManager $topicManager)
    {
        $this->topicManager = $topicManager;
    }

    /**
     * @param TopicWatchManager $topicWatchManager
     */
    public function injectTopicWatchManager(TopicWatchManager $topicWatchManager)
    {
        $this->topicWatchManager = $topicWatchManager;
    }

    /**
     * @param WwwDir $wwwDir
     */
    public function injectWwwDir(WwwDir $wwwDir)
    {
        $this->wwwDir = $wwwDir;
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
            $this->flashMessage('User mail was not found!',self::FLASH_MESSAGE_DANGER);
        }
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
        $this->getSessionManager()->deleteBySessionId($this->getSession()->getId());
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

        $this->template->thankCount      = $this->thanksManager->getCountCached();
        $this->template->topicCount      = $this->topicManager->getCountCached();
        $this->template->postCount       = $this->postManager->getCountCached();
        $this->template->watchTotalCount = $this->topicWatchManager->getCount();
        $this->template->userData        = $userData;
        $this->template->rank            = $rankUser;
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
        
        $watches = $this->topicWatchManager->getByRightJoinedFluent($user_id);
        
        $pag    = new PaginatorControl($watches, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no awcthes.', self::FLASH_MESSAGE_WARNING);
        }
           
        $this->template->watches = $watches->fetchAll();
    }
    
    public function renderList($page)
    {
        $users = $this->getManager()->getAllFluent();
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');   
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->users = $users;
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
        $form = $this->getBootStrapForm();
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
        $form = $this->getBootStrapForm();
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
}
