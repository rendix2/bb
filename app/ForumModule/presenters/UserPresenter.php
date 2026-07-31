<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Authorizator;
use App\Controls\BBMailer;
use App\Controls\BreadCrumbControl;
use App\Controls\ChangePasswordControl;
use App\Controls\DeleteAvatarControl;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Forms\ReportForm;
use App\Forms\SendMailToAdminForm;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserChangeUserNameForm;
use App\Forms\UserDeleteAvatarForm;
use App\Forms\UserResetPasswordForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Repository\LanguageRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\RankRepository;
use App\Model\Repository\SessionRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Model\Repository\UserRepository;
use App\Models\FavouriteUsersManager;
use App\Models\ModeratorManager;
use App\Models\UsersManager;
use App\services\AvatarService;
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
    /**
     * @var Avatars $avatar
     * @inject
     */
    public Avatars $avatar;

    /**
     * @var Ranks $ranks
     * @inject
     */
    public Ranks $ranks;

    /**
     * @var ModeratorManager $moderatorsManager
     * @inject
     */
    public ModeratorManager $moderatorsManager;

    /**
     *
     * @var BBMailer $bbMailer
     * @inject
     */
    public BBMailer $bbMailer;

    /**
     *
     * @var ChangePasswordFactory $changePasswordFactory
     * @inject
     */
    public ChangePasswordFactory $changePasswordFactory;

    /**
     *
     * @var DeleteAvatarFactory $deleteAvatarFactory
     * @inject
     */
    public DeleteAvatarFactory $deleteAvatarFactory;

    /**
     *
     * @var FavouriteUsersManager $favouriteUsersManager
     * @inject
     */
    public FavouriteUsersManager $favouriteUsersManager;

    /**
     *
     * @var StartDay $startDay
     * @inject
     */
    public StartDay $startDay;

    /**
     *
     * @var Users $users
     * @inject
     */
    public Users $users;


    public function __construct(
        UsersManager                            $manager,
        private readonly EntityManagerDecorator $em,
        private readonly ReportForm             $reportForm,
        private readonly UserResetPasswordForm  $userResetPasswordForm,
        private readonly UserChangeUserNameForm $userChangeUserNameForm,

        private readonly LanguageRepository     $languageRepository,
        private readonly TopicRepository        $topicRepository,
        private readonly PostRepository         $postRepository,
        private readonly UserRepository         $userRepository,

        private readonly ThankRepository      $thankRepository,
        private readonly TopicWatchRepository $topicWatchRepository,
        private readonly SessionRepository    $sessionRepository,
        private readonly RankRepository       $rankRepository,

        private readonly AvatarService          $avatarService,
    )
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
        $sessions = $this->sessionRepository->findBySession($this->getSession());

        foreach ($sessions as $session) {
            $this->em->remove($session);
        }

        $this->em->flush();

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
        $user = $this->checkUserParam($user_id);

        $res = $this->favouriteUsersManager->addByLeft($this->getUser()->getId(), [$user_id]);

        if ($res) {
            $this->flashMessage('User was added to favourites.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('this');
    }

    /**
     * @param int $user_id
     */
    public function handleUnSetFavourite($user_id): void
    {
        $user = $this->checkUserParam($user_id);

        $res = $this->favouriteUsersManager->delete($this->getUser()->getId(), $user_id);

        if ($res) {
            $this->flashMessage('User was deleted from favourites.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('this');
    }

    /**
     *
     */
    public function renderEdit(): void
    {
        $userEntity = [];

        if ($this->getUser()->isLoggedIn()) {
            $userEntity = $this->userRepository->findOneByUser($this->getUser());
        }

        $this['editUserForm']->setDefaults($userEntity);

        $this->template->avatarsDir = $this->avatar->getTemplateDir();
        $this->template->item = $userEntity;
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
    public function actionPosts($user_id, $page = 1): void
    {
        $user = $this->checkUserParam($user_id);

        $posts = $this->postRepository->findByUserId($user_id);

        $pag = new PaginatorControl($posts, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no posts.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $posts->fetchAll();
    }

    /**
     * @param int $user_id
     */
    public function renderProfile($user_id): void
    {
        $user = $this->checkUserParam($user_id);

        $ranks = $this->rankRepository->findAll();

        $specialRankEntity = $this->rankRepository
            ->findOneBy(
                [
                    'id' => $user->user_special_rank
                ]
            );

        $thanksCount = $this->thankRepository->count();
        $topicsCount = $this->topicRepository->count();
        $postsCount = $this->postRepository->count();

        $topicWatchesCount = $this->topicWatchRepository
            ->count();

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

        $this->template->specialRank = $specialRankEntity;
        $this->template->ranksDir = $this->ranks->getTemplateDir();
        $this->template->rank = $rankUser;
        $this->template->avatarsDir = $this->avatar->getTemplateDir();
        $this->template->moderatorForums = $this->moderatorsManager->getAllByLeftJoined($user_id);
        $this->template->thankCount = $thanksCount;
        $this->template->topicCount = $topicsCount;
        $this->template->postCount = $postsCount;
        $this->template->watchTotalCount = $topicWatchesCount;
        $this->template->userData = $user;
        $this->template->roles = Authorizator::ROLES;
        $this->template->isFavourite = $this->favouriteUsersManager->fullCheck($this->getUser()->getId(), $user_id);
        $this->template->user_id = $user_id;
        $this->template->favourites = $this->favouriteUsersManager->getAllByLeftJoined($user_id);
        $this->template->runningDays = $reg->diff($now)->days;
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function actionThanks($user_id, $page = 1)
    {
        $user = $this->checkUserParam($user_id);

        $thanks = $this->thanksManager->getFluentByUserJoinedTopic($user_id);
        $pag = new PaginatorControl($thanks, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no thanks.', self::FLASH_MESSAGE_WARNING);
        }

        $this->getTemplate()->thanks = $thanks->fetchAll();
    }

    /**
     * @param int $user_id user_id
     * @param int $page page
     */
    public function actionTopics($user_id, $page = 1)
    {
        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        if ($userEntity === null) {
            $this->error('User not found');
        }

        $topics = $this->topicRepository->findByUser($userEntity);

        $pag = new PaginatorControl($topics, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no topics.', self::FLASH_MESSAGE_WARNING);
        }

        $this->getTemplate()->topics = $topics;
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function actionWatches(int $user_id, int $page = 1): void
    {
        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        if ($userEntity === null) {
            $this->error('User not found');
        }

        $watches = $this->topicWatchRepository->findByUser($userEntity);

        $pag = new PaginatorControl($watches, 15, 5, $page);
        $this->addComponent($pag, 'paginator');

        if (!$pag->getCount()) {
            $this->flashMessage('User have no watches.', self::FLASH_MESSAGE_WARNING);
        }

        $this->getTemplate()->watches = $watches;
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

        $this->template->type = 1;
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

        $this->template->type = 3;
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

        $this->template->type = 5;
        $this->template->users = $users->fetchAll();
    }

    /**
     * @param int $user_id
     * @param int $page
     */
    public function actionFiles(int $user_id, int $page = 1)
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
    public function actionReport($user_id): void
    {
        $user = $this->checkUserParam($user_id);
    }

    protected function createComponentReportUserForm(): ReportForm
    {
        return new $this->reportForm;
    }

    protected function createComponentSendMailToAdmin(): SendMailToAdminForm
    {
        return new SendMailToAdminForm($this->translatorFactory, $this->getManager(), $this->bbMailer);
    }

    protected function createComponentChangePasswordControl(): UserChangePasswordForm
    {
        return $this->changePasswordFactory->getForum();
    }

    /**
     * @return UserDeleteAvatarForm
     */
    protected function createComponentDeleteAvatar(): UserDeleteAvatarForm
    {
        return $this->deleteAvatarFactory->getForum();
    }

    /**
     * @return UserResetPasswordForm
     */
    protected function createComponentResetPasswordForm(): UserResetPasswordForm
    {
        return $this->userResetPasswordForm;
    }

    protected function createComponentChangeUserNameForm(): UserChangeUserNameForm
    {
        return $this->userChangeUserNameForm;
    }


    protected function createComponentEditUserForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $userSettings = $this->users->get();

        $form->addText('user_name', 'User name:')
            ->setDisabled(!$userSettings['canChangeUserName']);

        $form->addSelect('user_lang_id', 'User language:', $this->languageRepository->findPairs());

        $form->addUpload('user_avatar', 'User avatar:')
            ->setHtmlAttribute('title', 'Max width: ' . $this->avatar->getMaxWidth() . 'px, max height: ' . $this->avatar->getMaxHeight() . 'px')
            ->setRequired(false)
            ->addRule(Form::Image, 'user_avatar_file_rule');

        $form->addTextArea('user_signature', 'User signature:');

        $form->addSubmit('send', 'Send');

        $form->onValidate[] = [$this, 'editUserValidate'];
        $form->onSuccess[] = [$this, 'editUserFormSuccess'];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function editUserValidate(Form $form, ArrayHash $values): void
    {
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function editUserFormSuccess(Form $form, ArrayHash $values): void
    {
        $user = $this->getUser();
        $user_id = $user->getId();

        try {
            $move = $this->avatarService->moveAvatar($values->user_avatar, $user_id);

            if ($move !== UsersManager::NOT_UPLOADED) {
                $values->user_avatar = $move;
            } else {
                unset($values->user_avatar);
            }
        } catch (InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage());
            unset($values->user_avatar);
        }

        if ($user->getId()) {
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
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
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
    protected function createComponentBreadCrumbPosts(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'menu_posts']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbProfile(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbSendMailToAdmin(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['text' => 'user_admin_contact']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbThanks(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'Thanks']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbTopics(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'menu_topics']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbWatches(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'watches']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     *
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:list', 'text' => 'menu_users'],
            2 => ['link' => 'User:profile', 'text' => 'menu_user', 'params' => [$this->getParameter('user_id')]],
            3 => ['text' => 'Report user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
