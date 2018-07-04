<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Controls\AppDir;
use App\Controls\Avatars;
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
     * @var SessionsManager $sessionsManager
     * @inject
     */
    public $sessionsManager;

    /**
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;

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
        
        $this->template->setTranslator($this->translatorFactory->adminTranslatorFactory());
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

        $this->template->countLogged = $count;
        $this->template->maxLogged   = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->template->loggedUsers = $loggedUsers;
        $this->template->dirSize     = $this->avatar->getDirSize();
        $this->template->avatarCount = $this->avatar->getImageCount();
    }
}
