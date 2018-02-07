<?php

namespace App\Presenters\Base;

use App\Presenters\Base\BasePresenter;
use Nette\Security\IUserStorage;

/**
 * Description of ManagerPresenter
 *
 * @author rendi
 */
abstract class ManagerPresenter extends BasePresenter {

    /**
     *
     * @var \App\Models\Manager $manager
     */
    private $manager;

    /**
     * 
     * @param \App\Models\Manager $manager
     */
    public function __construct(\App\Models\Manager $manager) {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * 
     * @return \App\Models\Manager $manager
     * 
     */
    protected function getManager() {
        return $this->manager;
    }

    public function startup() {
        parent::startup();

        $user = $this->getUser();

        if (!$this->user->isLoggedIn()) {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect(':Forum:Login:default', ['backlink' => $this->storeRequest()]);
        }
    }

}
