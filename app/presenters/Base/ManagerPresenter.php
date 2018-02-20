<?php

namespace App\Presenters\Base;

use App\Models\Manager;
use Nette\Security\IUserStorage;

/**
 * Description of ManagerPresenter
 *
 * @author rendi
 */
abstract class ManagerPresenter extends BasePresenter {

    /**
     *
     * @var Manager $manager
     */
    private $manager;
    private $sessionManager;

    /**
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager) {
        parent::__construct();

        $this->manager = $manager;
    }

    public function injectSessionsManager(\App\Models\SessionsManager $sessionManager) {
        $this->sessionManager = $sessionManager;
    }

    public function getSessionManager() {
        return $this->sessionManager;
    }

    /**
     *
     * @return Manager $manager
     *
     */
    protected function getManager() {
        return $this->manager;
    }

    public function startup() {
        parent::startup();

        $user = $this->getUser();

        if ($user->isLoggedIn()) {            
            $this->sessionManager->updateByUserId($this->getUser()->getId(), \Nette\Utils\ArrayHash::from(['session_last_activity' => time()]));
        } else {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
                $this->sessionManager->deleteBySessionId($this->getSession()->getId());
            }
            $this->redirect(':Forum:Login:default', ['backlink' => $this->storeRequest()]);
        }
    }

}
