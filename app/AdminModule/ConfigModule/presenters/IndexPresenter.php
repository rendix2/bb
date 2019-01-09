<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;
use App\Settings\Avatars;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
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
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;
   
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
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     * IndexPresenter beforeRender.
     */
    public function beforeRender()
    {
        parent::beforeRender();
        
        $this->template->setTranslator($this->translatorFactory->getAdminTranslator());
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
