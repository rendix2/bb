<?php

namespace App\AdminModule\Presenters;

use App\Controls\AppDir;
use App\Presenters\Base\BasePresenter;
use App\Translator;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends BasePresenter
{
    const MAX_LOGGED_IN_USERS_TO_SHOW = 200;
    
    /**
     * @var AppDir $appDir
     */
    private $appDir;
    
    private $sessionsManager;

    /**
     * @param AppDir $appDir
     */
    public function injectAppDir(AppDir $appDir){
        $this->appDir = $appDir;
    }
    
    public function injectSessionManager(\App\Models\SessionsManager $sessionManager){
        $this->sessionsManager = $sessionManager;
    }
    
    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }

        $lang_name = $this->getUser()->getIdentity()->getData()['lang_file_name'];

        //$this->adminTranslator = new Translator($this->appDir,'Admin', $lang_name);
    }
    

        /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();
        $lang_name = $this->getUser()->getIdentity()->getData()['lang_file_name'];

        $this->template->setTranslator(new Translator($this->appDir,'admin', $lang_name));
    }

    /**
     *
     */
    public function renderDefault()
    {
        $count = $this->sessionsManager->getCountOfLoggedUsers();
        
        if ( $count <= self::MAX_LOGGED_IN_USERS_TO_SHOW ){
                    $loggedUsers = $this->sessionsManager->getLoggedUsers();
        }else{
            $loggedUsers = null;
        }
     
        $this->template->countLogged = $count;
        $this->template->maxLogged   = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->template->loggedUsers = $loggedUsers;
    }

}
