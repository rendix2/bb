<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Controls\AppDir;
use App\Settings\Avatars;
use App\Models\SessionsManager;
use App\Presenters\Base\BasePresenter;


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
        $this->getUser()->getStorage()->setNamespace('beckend');
        
        parent::checkRequirements($element);

        if (!$this->getUser()->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$this->getUser()->isInRole('admin')) {
            $this->error('You are not admin.');
        }
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
