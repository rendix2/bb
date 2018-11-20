<?php

namespace App\Presenters\Base;

use App\Models\SessionsManager;
use Nette\Security\IUserStorage;
use Nette\Utils\ArrayHash;

/**
 * Description of AuthenticatedPresenter
 *
 * @author rendix2
 * @package App\Presenters\Base
 */
abstract class AuthenticatedPresenter extends BasePresenter
{
    
    /**
     * sessions manager
     *
     * @var SessionsManager $sessionsManager
     * @inject
     */
    public $sessionsManager;
    
    /**
     *
     */
    public function __destruct()
    {
        $this->sessionsManager = null;
        
        parent::__destruct();
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->user;

        if ($user->loggedIn) {
            $this->sessionsManager->updateByUser($user->id, ArrayHash::from(['session_last_activity' => time()]));
        } else {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->sessionsManager->deleteBySession($this->getSession()->getId());
                $this->sessionsManager->deleteByUser($user->id);
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }

            if ($this->presenter->getName() !== 'Forum:Index') {
                $this->sessionsManager->deleteBySession($this->getSession()->getId());
                $this->redirect(':Forum:Login:default', ['loginForm-backlink' => $this->storeRequest()]);
            }
        }
    }
}
