<?php

namespace App\Presenters\Base;

use App\Models\BansManager;
use App\Services\TranslatorFactory;
use Nette;
use App\Controls\BootstrapForm;
use Nette\Http\IResponse;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use \Nextras\Application\UI\SecuredLinksPresenterTrait;
    
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
