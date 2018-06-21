<?php

namespace App\AdminModule\Presenters;

use App\Controls\AppDir;
use App\Controls\BootstrapForm;
use App\Presenters\Base\BasePresenter;
use App\Translator;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Localization\ITranslator;
use Nette\Security\IUserStorage;
use Nette\Utils\ArrayHash;

/**
 * Description of CachePresenter
 *
 * @author rendi
 */
class CachePresenter extends BasePresenter
{
    /**
     * @var ITranslator $translator
     */
    private $translator;
    
    /**
     * @var AppDir $appDir
     */
    private $appDir;
    
    /**
     *
     * @var IStorage $cache
     */
    private $cache;

    /**
     * @param AppDir $appDir
     */
    public function injectAppDir(AppDir $appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @param IStorage $storage
     */
    public function injectCache(IStorage $storage)
    {
        $this->cache = new Cache($storage);
    }

    /**
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
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$this->user->isLoggedIn()) {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Login:default', ['backlink' => $this->storeRequest()]);
        }
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator(
            $this->translator = new Translator(
                $this->appDir,
                'Admin',
                $this->getUser()
                    ->getIdentity()
                    ->getData()['lang_file_name']
            )
        );
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addSubmit('Delete_all', 'Delete all cache');

        $form->onSuccess[] = [$this, 'success'];

        return $form;
    }
}

