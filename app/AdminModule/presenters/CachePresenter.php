<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Models\CacheManager;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of CachePresenter
 *
 * @author rendix2
 * @method CacheManager getManager()
 * @package App\AdminModule\Presenters
 */
class CachePresenter extends AdminPresenter
{
    
    /**
     *
     * @var Cache $cache
     */
    private Cache $cache;

    /**
     * CachePresenter constructor.
     *
     * @param CacheManager $manager
     * @param IStorage     $storage
     */
    public function __construct(CacheManager $manager, IStorage $storage)
    {
        parent::__construct($manager);
        
        $this->cache = new Cache($storage);
    }

    /**
     * CachePresenter startup.
     */
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

    /**
     * CachePresenter beforeRender.
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->translatorFactory->getAdminTranslator());
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->addSubmit('delete_all', 'Delete all cache');

        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }
    
    /**
     * deletes ALL cache
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $this->cache->clean([Cache::All => Cache::All]);
        $this->flashMessage('Cache was deleted.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('this');
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        return $this->gf;
    }
}
