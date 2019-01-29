<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Authorizator;
use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Controls\PaginatorControl;
use App\Forms\ReportForm;
use App\Forms\SendMailToAdminForm;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserChangeUserNameForm;
use App\Forms\UserDeleteAvatarForm;
use App\Forms\UserResetPasswordForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Models\FavouriteUsersManager;
use App\Models\LanguagesManager;
use App\Models\ModeratorsManager;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\TopicWatchManager;
use App\Models\Traits\UsersTrait;
use App\Models\UsersManager;
use App\Services\ChangePasswordFactory;
use App\Services\DeleteAvatarFactory;
use App\Settings\Avatars;
use App\Settings\Ranks;
use App\Settings\StartDay;
use App\Settings\Users;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of UserProfilePresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\ForumModule\Presenters
 */
class UserPresenter extends BaseForumPresenter
{
    use UsersTrait;
    //use \App\Models\Traits\TopicsTrait;
    //use \App\Models\Traits\PostTrait;
    
    /**
     * @var LanguagesManager $languagesManager
     * @inject
     */
    public $languagesManager;

    /**
     * @var RanksManager $ranksManager
     * @inject
     */
    public $ranksManager;

    /**
     * @var TopicWatchManager $topicWatchManager
     * @inject
     */
    public $topicWatchManager;
    
    /**
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     * @var Ranks $ranks
     * @inject
     */
    public $ranks;
    
    /**
     * @var ModeratorsManager $moderatorsManager
     * @inject
     */
    public $moderatorsManager;

    /**
     *
     * @var BBMailer $bbMailer
     * @inject
     */
    public $bbMailer;
    
    /**
     *
     * @var ChangePasswordFactory $changePasswordFactory
     * @inject
     */
    public $changePasswordFactory;
    
    /**
     *
     * @var DeleteAvatarFactory $deleteAvatarFactory
     * @inject
     */
    public $deleteAvatarFactory;
    
    /**
     *
     * @var FavouriteUsersManager $favouriteUsersManager
     * @inject
     */
    public $favouriteUsersManager;
    
    /**
     *
     * @var StartDay $startDay
     * @inject
     */
    public $startDay;

    /**
     *
     * @var Users $users
     * @inject
     */
    public $users;
    
    /**
     *
     * @var ReportsManager $reportsManager
     * @inject
     */
    public $reportsManager;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * UserPresenter destructor.
     */
    public function __destruct()
    {
        $this->usersManager          = null;
        $this->topicsManager         = null;
        $this->postsManager          = null;
        $this->languagesManager      = null;
        $this->ranksManager          = null;
        $this->topicWatchManager     = null;
        $this->thanksManager         = null;
        $this->avatar                = null;
        $this->ranks                 = null;
        $this->moderatorsManager     = null;
        $this->bbMailer              = null;
        $this->changePasswordFactory = null;
        $this->deleteAvatarFactory   = null;
        $this->favouriteUsersManager = null;
        $this->startDay              = null;
        $this->users                 = null;
        $this->reportsManager        = null;

        parent::__destruct();
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
        $this->sessionsManager->deleteBySession($this->session->getId());
        $this->user->logout(true);

        $this->flashMessage('Successfully logged out.', self::FLASH_MESSAGE_SUCCESS);
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
     * @param int $user_id
     */
    public function handleSetFavourite($user_id)
    {
        $user = $this->checkUserParam($user_id);

        $res = $this->favouriteUsersManager->addByLeft($this->user->id, [$user_id]);
        
        if ($res) {
            $this->flashMessage('User was added to favourites.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('this');
    }

    /**
     * @param int $user_id
     */
    public function handleUnSetFavourite($user_id)
    {
        $user = $this->checkUserParam($user_id);

        $res = $this->favouriteUsersManager->delete($this->user->id, $user_id);
        
        if ($res) {
            $this->flashMessage('User was deleted from favourites.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('this');
    }

    /**
     *
     */
    public function renderEdit()
    {
        $user     = $this->user;
        $userData = [];

        if ($user->loggedIn) {
            $userData = $this->getManager()->getById($user->id);
        }

        $this['editUserForm']->setDefaults($userData);
       
        $this->template->avatarsDir = $this->avatar->getTemplateDir();
        $this->template->item       = $userData;
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
    public function actionPosts($user_id, $page = 1)
    {
        $user = $this->checkUserParam($user_id);

        $posts = $this->postsManager->getFluentByUser($user_id);
        $pag   = new PaginatorControl($posts, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no posts.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $posts->fetchAll();
    }

    /**
     * @param int $user_id
     */
    public function renderProfile($user_id)
    {
        $user = $this->checkUserParam($user_id);

        $ranks    = $this->ranksManager->getAllCached();
        $rankUser = null;

        foreach ($ranks as $rank) {
            $post_count = $user->getUser_post_count();

            if ($post_count >= $rank->rank_from && $post_count <= $rank->rank_to) {
                $rankUser = $rank;
                break;
            }
        }
        
        $reg = DateTime::from($user->getUser_register_time());
        $now = new DateTime();

        $this->template->specialRank     = $this->ranksManager->getById($user->user_special_rank);
        $this->template->ranksDir        = $this->ranks->getTemplateDir();
        $this->template->rank            = $rankUser;
        $this->template->avatarsDir      = $this->avatar->getTemplateDir();
        $this->template->moderatorForums = $this->moderatorsManager->getAllByLeftJoined($user_id);
        $this->template->thankCount      = $this->thanksManager->getCountCached();
        $this->template->topicCount      = $this->topicsManager->getCountCached();
        $this->template->postCount       = $this->postsManager->getCountCached();
        $this->template->watchTotalCount = $this->topicWatchManager->getCount();
        $this->template->userData        = $user;
        $this->template->roles           = Authorizator::ROLES;
        $this->template->isFavourite     = $this->favouriteUsersManager->fullCheck($this->user->id, $user_id);
        $this->template->user_id         = $user_id;
        $this->template->favourites      = $this->favouriteUsersManager->getAllByLeftJoined($user_id);
        $this->template->runningDays     = $reg->diff($now)->days;
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function actionThanks($user_id, $page = 1)
    {
        $user = $this->checkUserParam($user_id);

        $thanks = $this->thanksManager->getFluentByUserJoinedTopic($user_id);
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
    public function actionTopics($user_id, $page = 1)
    {
        $user = $this->checkUserParam($user_id);

        $topics = $this->topicsManager->getFluentByUser($user_id);
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
    public function actionWatches($user_id, $page = 1)
    {
        $user = $this->checkUserParam($user_id);
        
        $watches = $this->topicWatchManager->getFluentByRightJoined($user_id);
        
        $pag    = new PaginatorControl($watches, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no watches.', self::FLASH_MESSAGE_WARNING);
        }
           
        $this->template->watches = $watches->fetchAll();
    }

    /**
     * @param int $page
     */
    public function actionList($page)
    {
        $users = $this->getManager()->getAllFluent();
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 1;
        $this->template->users = $users->fetchAll();
    }

    /**
     * @param int $page
     */
    public function renderModeratorList($page)
    {
        $this->setView('list');
        
                $users = $this->getManager()
                ->getAllFluent()
                ->where('[user_role_id] = %i', 3);
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 3;
        $this->template->users = $users->fetchAll();
    }

    /**
     * @param int $page
     */
    public function renderAdminList($page)
    {
        $this->setView('list');
        
        $users = $this->getManager()
                ->getAllFluent()
                ->where('[user_role_id] = %i', 5);
        
        $pag = new PaginatorControl($users, 15, 5, $page);
        $this->addComponent($pag, 'paginator');
        
        if (!$pag->getCount()) {
            $this->flashMessage('No users.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->type  = 5;
        $this->template->users = $users->fetchAll();
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function actionFiles($user_id, $page = 1)
    {
    }

    /**
     *
     */
    public function renderRegister()
    {
        // todo
    }

    /**
     *
     */
    public function renderSendMailToAdmin()
    {
        // TODO
    }

    /**
     * @param int $user_id
     */
    public function actionReport($user_id)
    {
        $user = $this->checkUserParam($user_id);
    }

    /**
     * @return ReportForm
     */
    protected function createComponentReportUserForm()
    {
        return new ReportForm($this->reportsManager);
    }

    /**
     * @return SendMailToAdminForm
     */
    protected function createComponentSendMailToAdmin()
    {
        return new SendMailToAdminForm($this->translatorFactory, $this->getManager(), $this->bbMailer);
    }

    /**
     * @return UserChangePasswordForm
     */
    protected function createComponentChangePasswordControl()
    {
        return $this->changePasswordFactory->getForum();
    }

    /**
     * @return UserDeleteAvatarForm
     */
    protected function createComponentDeleteAvatar()
    {
        return $this->deleteAvatarFactory->getForum();
    }
    
    /**
     * @return UserResetPasswordForm
     */
    protected function createComponentResetPasswordForm()
    {
        return new UserResetPasswordForm($this->translatorFactory, $this->getManager());
    }

    /**
     *
     * @return UserChangeUserNameForm
     */
    protected function createComponentChangeUserNameForm()
    {
        return new UserChangeUserNameForm($this->getManager(), $this->user);
    }
       
    /**
     * @return BootstrapForm
     */
    protected function createComponentEditUserForm()
    {
        $form         = $this->getBootstrapForm();
        $userSettings = $this->users->get();

        $form->addText(
            'user_name',
            'User name:'
        )->setDisabled(!$userSettings['canChangeUserName']);
        $form->addSelect(
            'user_lang_id',
            'User language:',
            $this->languagesManager->getAllPairsCached('lang_name')
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
    public function editUserFormSuccess(Form $form, ArrayHash $values)
    {
        $user    = $this->user;
        $user_id = $user->id;
        
        try {
            $move = $this->getManager()->moveAvatar($values->user_avatar, $user_id);
            
            if ($move !== UsersManager::NOT_UPLOADED) {
                $values->user_avatar = $move;
            } else {
                unset($values->user_avatar);
            }
        } catch (InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage());
            unset($values->user_avatar);
        }

        if ($user->loggedIn) {
            $result = $this->getManager()->update($user_id, $values);
        } else {
            $result = $this->getManager()->add($values);
        }

        if ($result) {
            $this->flashMessage('User was saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('User:edit');
    }
    
    /**
     * BREAD CRUMBS
     */

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbPosts()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['link' => 'User:profile',  'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'menu_posts']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbProfile()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbSendMailToAdmin()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['text' => 'user_admin_contact']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbThanks()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['link' => 'User:profile',  'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'Thanks']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbTopics()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['link' => 'User:profile',  'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'menu_topics']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbWatches()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['link' => 'User:profile',  'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'watches']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list',     'text' => 'menu_users'],
            2 => ['link' => 'User:profile',  'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'Report user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
