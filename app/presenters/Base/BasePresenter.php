<?php

namespace App\Presenters\Base;

use App\Authorizator;
use App\Models\BansManager;
use App\Services\TranslatorFactory;
use Nette;
use App\Controls\BootstrapForm;
use Nette\Http\IResponse;
use Nextras\Application\UI\SecuredLinksPresenterTrait;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use SecuredLinksPresenterTrait;
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_SUCCESS = 'success';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_DANGER = 'danger';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_WARNING = 'warning';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_INFO = 'info';
    
    /**
     * @var BansManager $banManager
     * @inject
     */
    public $banManager;
      
    /**
     * @var BootstrapForm $bootStrapForm
     */
    private $bootstrapForm;

    /**
     * @var TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;

    /**
     * BasePresenter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrapForm = BootstrapForm::create();
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();
      
        $this->banUser();
    }
    
    /**
     * create BootstrapForm
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        return new BootstrapForm();
    }
    
    /**
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        return $this->bootstrapForm;
    }
    
    /**
     * ban user
     */
    private function banUser()
    {
        $bans     = $this->banManager->getAllCached();
        $identity = $this->getUser()->getIdentity();
        $user     = $this->getUser();
        
        // if not main admin or role is not admin, so you can not ban admin, if some problem....
        if ($user->getId() !== 1 || !in_array(Authorizator::ROLES[5], $this->getUser()->getRoles(), true)) {
            foreach ($bans as $ban) {
                if ($identity && $this->getUser()->isLoggedIn()) {
                    if ($ban->ban_email === $identity->getData()['user_email'] || $ban->ban_user_name === $identity->getData()['user_name']) {
                        $this->error('Banned', IResponse::S403_FORBIDDEN);
                    }
                }

                if ($ban->ban_ip === $this->getHttpRequest()->getRemoteAddress()) {
                    $this->error('Banned', IResponse::S403_FORBIDDEN);
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function checkLoggedIn()
    {
        $identity = $this->getUser()->getIdentity();
        
        if (!$identity) {
            return false;
        }
        
        return $this->getUser()->isLoggedIn();
    }

    /**
     * @return bool
     */
    protected function checkUserLoggedIn()
    {
        return $this->checkAdminLoggedIn() && !in_array('guest', $this->getUser()->getRoles(), true);
    }

    /**
     * @return bool
     */
    protected function checkJuniorAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('juniorAdmin');
    }

    /**
     * @return bool
     */
    protected function checkAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('admin');
    }
}
