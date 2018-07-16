<?php

namespace App\Presenters\Base;

use App\Models\Manager;
use App\Models\SessionsManager;
use Nette\Security\IUserStorage;
use Nette\Utils\ArrayHash;

/**
 * Description of ManagerPresenter
 *
 * @author rendi
 */
abstract class ManagerPresenter extends BasePresenter
{
    /**
     * manager
     *
     * @var Manager $manager
     */
    private $manager;
    
    /**
     * session manager
     *
     * @var SessionsManager $sessionManager
     * @inject
     */
    public $sessionManager;

    /**
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     *
     * @return Manager $manager
     */
    protected function getManager()
    {
        return $this->manager;
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if ($user->isLoggedIn()) {
            $this->sessionManager->updateByUser($user->getId(), ArrayHash::from(['session_last_activity' => time()]));
        } else {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
                $this->sessionManager->deleteBySession($this->getSession()->getId());
            }
            $this->redirect(':Forum:Login:default', ['loginForm-backlink' => $this->storeRequest()]);
        }
    }
}
