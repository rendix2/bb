<?php

namespace App\Presenters\Base;

use App\Models\SessionsManager;
use Nette\Security\IUserStorage;
use Nette\Utils\ArrayHash;

/**
 * Description of AuthenticatedPresenter
 *
 * @author rendi
 */
abstract class AuthenticatedPresenter extends BasePresenter
{
    
    /**
     * session manager
     *
     * @var SessionsManager $sessionManager
     * @inject
     */
    public $sessionsManager;

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if ($user->isLoggedIn()) {
            $this->sessionsManager->updateByUser($user->getId(), ArrayHash::from(['session_last_activity' => time()]));
        } else {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
                $this->sessionsManager->deleteBySession($this->getSession()->getId());
            }
            
            $this->redirect(':Forum:Login:default', ['loginForm-backlink' => $this->storeRequest()]);
        }
    }
}
