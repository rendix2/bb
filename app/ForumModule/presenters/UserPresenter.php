<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Controls\PaginatorControl;
use App\Forms\SendMailToAdminForm;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserChangeUserNameForm;
use App\Forms\UserDeleteAvatarForm;
use App\Forms\UserResetPasswordForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Models\FavouriteUsersManager;
use App\Models\LanguagesManager;
use App\Models\ModeratorsManager;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Models\UsersManager;
use App\Services\ChangePasswordFactory;
use App\Services\DeleteAvatarFactory;
use App\Settings\Avatars;
use App\Settings\Ranks;
use App\Settings\Users;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of UserProfilePresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 */
class UserPresenter extends BaseForumPresenter
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
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     * @var Ranks $rank
     * @inject
     */
    public $rank;
    
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
     * @var \App\Settings\StartDay $startDay
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
        $this->sessionsManager->deleteBySession($this->getSession()->getId());
        $this->getUser()->logout(true);

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
        $res = $this->favouriteUsersManager->addByLeft($this->getUser()->getId(), [$user_id]);
        
        if ($res) {
            $this->flashMessage('User was added to favourites.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        //$this->redrawControl('favourite');
        $this->redirect('this');
    }

    /**
     * @param int $user_id
     */
    public function handleUnSetFavourite($user_id)
    {
        $res = $this->favouriteUsersManager->fullDelete($this->getUser()->getId(), $user_id);
        
        if ($res) {
            $this->flashMessage('User was deleted from favourites.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        //$this->redrawControl('favourite');
        $this->redirect('this');
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
    public function renderPosts($user_id, $page = 1)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->getManager()->getById($user_id);

        if (!$user) {
            $this->flashMessage('User does not exists.', self::FLASH_MESSAGE_DANGER);
        }

        $posts = $this->postManager->getByUser($user_id);
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
        
        $reg = DateTime::from($userData->user_register_time);
        $now = new DateTime();
        
        $this->template->ranksDir        = $this->rank->getTemplateDir();
        $this->template->avatarsDir      = $this->avatar->getTemplateDir();
        $this->template->moderatorForums = $this->moderatorsManager->getAllJoinedByLeft($user_id);
        $this->template->thankCount      = $this->thanksManager->getCountCached();
        $this->template->topicCount      = $this->topicManager->getCountCached();
        $this->template->postCount       = $thfis->postManager->getCountCached();
        $this->template->watchTotalCount = $this->topicWatchManager->getCount();
        $this->template->userData        = $userData;
        $this->template->rank            = $rankUser;
        $this->template->roles           = \App\Authorizator::ROLES;
        $this->template->isFavourite     = $this->favouriteUsersManager->fullCheck($this->getUser()->getId(), $user_id);
        $this->template->user_id         = $user_id;
        $this->template->favourites      = $this->favouriteUsersManager->getAllJoinedByLeft($user_id);
        $this->template->runningDays     = $reg->diff($now)->days;
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

        $thanks = $this->thanksManager->getFluentJoinedTopicByUser($user_id);
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

        $topics = $this->topicManager->getFluentByUser($user_id);
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
            $this->flashMessage('User have no watches.', self::FLASH_MESSAGE_WARNING);
        }
           
        $this->template->watches = $watches->fetchAll();
    }

    /**
     * @param int $page
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
     * @param int $page
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
     * @param int $page
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
     * @param $user_id
     */
    public function actionReport($user_id)
    {
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentReportUserForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translatorFactory->forumTranslatorFactory());

        $form->addTextArea('report_text', 'Report text:');
        $form->addSubmit('send', 'Report user');
        $form->onSuccess[] = [$this, 'reportUserSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function reportUserSuccess(Form $form, ArrayHash $values)
    {
        $user_id = $this->getParameter('user_id');

        $data = [
            'report_user_id'          => $this->getUser()->getId(),
            'report_reported_user_id' => $user_id,
            'report_time'             => time()
        ];

        $res = $this->reportsManager->add(ArrayHash::from($data));

        if ($res) {
            $this->flashMessage('User was reported.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('User was not reported.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect('User:profile', $user_id);
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
        return new UserChangeUserNameForm($this->getManager(), $this->getUser());
    }
       
    /**
     * @return BootstrapForm
     */
    protected function createComponentEditUserForm()
    {
        $form         = $this->getBootstrapForm();
        $userSettings = $this->users->getUser();

        $form->addText(
            'user_name',
            'User name:'
        )->setDisabled(!$userSettings['canChangeUserName']);
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
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editUserFormSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->getUser();
        
        try {
            $move = $this->getManager()->moveAvatar($values->user_avatar, $user->getId());
            
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

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbPosts()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'menu_posts']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbProfile()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbSendMailToAdmin()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['text' => 'user_admin_contact']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbThanks()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'params' => [$this->getParameter('user_id')], 'text' => 'menu_user'],
            3 => ['text' => 'Thanks']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbTopics()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'params' => [$this->getParameter('user_id')], 'text' => 'menu_user'],
            3 => ['text' => 'menu_topics']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbWatches()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'params' => [$this->getParameter('user_id')], 'text' => 'menu_user'],
            3 => ['text' => 'watches']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }
    
    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'params' => [$this->getParameter('user_id')], 'text' => 'menu_user'],
            3 => ['text' => 'Report user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }
}
