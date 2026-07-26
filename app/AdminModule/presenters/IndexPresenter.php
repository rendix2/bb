<?php

namespace App\AdminModule\Presenters;

use App\Database\EntityManagerDecorator;
use App\Models\SessionManager;
use App\Presenters\Base\BasePresenter;
use App\Settings\Avatars;
use App\Settings\CacheDir;
use Nette\DI\Attributes\Inject;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class IndexPresenter extends BasePresenter
{
    const int MAX_LOGGED_IN_USERS_TO_SHOW = 200;
    
    #[Inject]
    public SessionManager $sessionsManager;

    #[Inject]
    public Avatars $avatar;

    #[Inject]
    public CacheDir $cacheDir;

    public function __construct(
        private readonly EntityManagerDecorator $em,
    )
    {
    }

    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element): void
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if (!$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     * IndexPresenter beforeRender.
     *
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        
        $this->getTemplate()->setTranslator($this->translatorFactory->getAdminTranslator());
    }

    /**
     *
     */
    public function renderDefault(): void
    {
        $count = $this->sessionsManager->getCountOfLoggedUsers();

        $loggedUsers = $count <= self::MAX_LOGGED_IN_USERS_TO_SHOW ? $this->sessionsManager->getLoggedUsers() : null;

        $this->template->countLogged   = $count;
        $this->template->maxLogged     = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->template->loggedUsers   = $loggedUsers;
        $this->template->avatarDirSize = $this->avatar->getDirSize();
        $this->template->avatarCount   = $this->avatar->getImageCount();
        $this->template->cacheDirSize  = $this->cacheDir->getDirSize();
    }
    
    /**
     * truncate sessions
     */
    public function actionDeleteSessions(): void
    {
        $res = $this->sessionsManager->truncateSessions();
        
        if ($res) {
            $this->flashMessage('Sessions were deleted.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Index:default');
    }

    /**
     * logout user
     */
    public function actionLogout(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('User was logged out.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect(':Forum:Index:default');
    }
}
