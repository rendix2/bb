<?php

namespace App\Presenters\Base;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
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
     * @var \App\Models\BansManager $banManager 
     * @inject
     */
    public $banManager;
    
    /**
     * @var \Nette\Http\Request $httpRequest
     * @inject
     */
    public $httpRequest;
    
    public function startup() {
        parent::startup();
        
        $bans = $this->banManager->getAllCached();     
        $identity = $this->getUser()->getIdentity();
        
        foreach($bans as $ban) {
            if ($identity && $this->getUser()->isLoggedIn()) {
                if ($ban->ban_email === $identity->getData()['user_email'] || $ban->ban_user_name === $identity->getData()['user_name']) {
                    $this->error('Banned', Nette\Http\IResponse::S403_FORBIDDEN);
                }               
            }

            if ($ban->ban_ip === $this->httpRequest->getRemoteAddress()) {
                $this->error('Banned', Nette\Http\IResponse::S403_FORBIDDEN);  
            }
        }
    }

}
