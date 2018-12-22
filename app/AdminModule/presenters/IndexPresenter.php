<?php

namespace App\AdminModule\Presenters;

use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use App\Settings\Avatars;
use App\Settings\CacheDir;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class IndexPresenter extends BasePresenter
{
    /**
     * @var int
     */
    const MAX_LOGGED_IN_USERS_TO_SHOW = 200;
    
    /**
     * @var SessionsManager $sessionsManager
     * @inject
     */
    public $sessionsManager;
    
    /**
     *
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     * @var CacheDir $cacheDir
     * @inject
     */
    public $cacheDir;
    
    /**
     *
     */
    public function __destruct()
    {
        $this->sessionsManager = null;
        $this->avatar          = null;
        $this->cacheDir        = null;
        
        parent::__destruct();
    }

    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $user = $this->user;
        
        $user->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if (!$user->loggedIn) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     * before render
     * sets translator
     */
    public function beforeRender()
    {
        parent::beforeRender();
        
        $this->template->setTranslator($this->translatorFactory->createAdminTranslatorFactory());
    }

    /**
     *
     */
    public function renderDefault()
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
    public function actionDeleteSessions()
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
    public function actionLogout()
    {
        $this->user->logout(true);
        $this->flashMessage('User was logged out.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect(':Forum:Index:default');
    }
}
