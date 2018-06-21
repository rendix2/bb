<?php

namespace App\AdminModule\Presenters;

use App\Controls\AppDir;
use App\Controls\Avatars;
use App\Controls\CacheDir;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use App\Translator;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends BasePresenter
{
    /**
     *
     */
    const MAX_LOGGED_IN_USERS_TO_SHOW = 200;
    
    /**
     * @var AppDir $appDir
     */
    private $appDir;
    
    /**
     * @var SessionsManager $sessionsManager
     */
    private $sessionsManager;
    
    /**
     *
     * @var Avatars $avatar
     */
    private $avatar;
    
    
    /**
     * @var CacheDir $cacheDir
     */
    private $cacheDir;

    /**
     * @param CacheDir $cacheDir
     */
    public function injectCacheDir(CacheDir $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param AppDir $appDir
     */
    public function injectAppDir(AppDir $appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @param SessionsManager $sessionManager
     */
    public function injectSessionManager(SessionsManager $sessionManager)
    {
        $this->sessionsManager = $sessionManager;
    }
    
    /**
     *
     * @param Avatars $avatar
     */
    public function injectAvatars(Avatars $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }

        $lang_name = $user->getIdentity()->getData()['lang_file_name'];
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();
        
        $lang_name = $this->getUser()
                         ->getIdentity()
                         ->getData()['lang_file_name'];

         $translator = new Translator($this->appDir, 'admin', $lang_name);
        
        $this->template->setTranslator($translator);
    }

    /**
     *
     */
    public function renderDefault()
    {
        $count = $this->sessionsManager->getCountOfLoggedUsers();

        if ($count <= self::MAX_LOGGED_IN_USERS_TO_SHOW) {
            $loggedUsers = $this->sessionsManager->getLoggedUsers();
        } else {
            $loggedUsers = null;
        }

        $this->template->countLogged   = $count;
        $this->template->maxLogged     = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->template->loggedUsers   = $loggedUsers;
        $this->template->avatarDirSize = $this->avatar->getDirSize();
        $this->template->avatarCount   = $this->avatar->getCountOfAvatars();
        $this->template->cacheDirSize  = $this->cacheDir->getDirSize();
    }
}
