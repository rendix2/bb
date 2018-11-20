<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\CacheManager;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\IUserStorage;
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
    private $cache;

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
     * 
     */
    public function __destruct()
    {
        $this->cache = null;
        
        parent::__destruct();
    }

    /**
     * cache startup
     */
    public function startup()
    {
        parent::startup();
        
        $user = $this->user;

        if (!$user->loggedIn) {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }

            $this->redirect('Login:default', ['backlink' => $this->storeRequest()]);
        }
    }

    /**
     * cache before render
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translatorFactory->createAdminTranslatorFactory());
    }

    /**
     * creates form to delete all cache
     *
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        $form->addSubmit('Delete_all', 'Delete all cache');

        $form->onSuccess[] = [$this, 'success'];

        return $form;
    }
    
    /**
     * deletes ALL cache
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function success(Form $form, ArrayHash $values)
    {
        $this->cache->clean([Cache::ALL => Cache::ALL]);
        $this->flashMessage('Cache was deleted.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('this');
    }
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;
    }
}
