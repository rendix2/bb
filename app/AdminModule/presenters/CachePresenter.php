<?php

namespace App\AdminModule\Presenters;

use App\Controls\AppDir;
use App\Controls\BootstrapForm;
use App\Models\CacheManager;
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
class CachePresenter extends Base\AdminPresenter
{
    /**
     * @var ITranslator $translator
     */
    public $translator;
    
    /**
     * @var AppDir $appDir
     * @inject
     */
    public $appDir;
    
    /**
     *
     * @var Cache $cache
     */
    private $cache;

    /**
     * CachePresenter constructor.
     *
     * @param CacheManager $manager
     */
    public function __construct(CacheManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param IStorage $storage
     */
    public function injectCache(IStorage $storage)
    {
        $this->cache = new Cache($storage);
    }

    /**
     * cache startup
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
     * cache before render
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->translator = $this->translatorFactory->adminTranslatorFactory();
        $this->template->setTranslator($this->translator);
    }

    /**
     * creates form to delete all cache
     *
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
}
