<?php

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of CachePresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class CachePresenter extends Presenter
{
    
    /**
     *
     * @var Cache $cache
     */
    private Cache $cache;

    public function __construct(IStorage $storage)
    {
        parent::__construct();
        
        $this->cache = new Cache($storage);
    }

    public function startup()
    {
        parent::startup();
        
        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            if ($user->getLogoutReason() ===  User::LogoutInactivity) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }

            $this->redirect('Login:default', ['backlink' => $this->storeRequest()]);
        }
    }


    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->addSubmit('delete_all', 'Delete all cache');

        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }
    
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $this->cache->clean([Cache::All => Cache::All]);
        $this->flashMessage('Cache was deleted.', 'success');
        $this->redirect('this');
    }
}
