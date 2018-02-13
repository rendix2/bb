<?php

namespace App\Presenters\Base;

use App\Models\Manager;
use Nette\Security\IUserStorage;

/**
 * Description of ManagerPresenter
 *
 * @author rendi
 */
abstract class ManagerPresenter extends BasePresenter
{

    /**
     *
     * @var Manager $manager
     */
    private $manager;

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
     *
     */
    protected function getManager()
    {
        return $this->manager;
    }

    public function startup()
    {
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
