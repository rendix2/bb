<?php

namespace App\Services;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Controls\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of ChangePasswordFactory
 *
 * @author rendi
 */
class ChangePasswordFactory
{
    /**
     *
     * @var UsersManager $userManager 
     */
    private $userManager;
    
    /**
     * @var User $user
     */
    private $user;
    
    /**
     *
     * @var Users $users
     */
    private $users;
    
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private $translatorFactory;
    
    /**
     * 
     * @param UsersManager $userManager
     * @param TranslatorFactory $translatorFactory
     * @param User $user
     * @param Users $users
     */
    public function __construct(UsersManager $userManager, TranslatorFactory $translatorFactory, User $user, Users $users)
    {
        $this->userManager       = $userManager;
        $this->user              = $user;
        $this->users             = $users;
        $this->translatorFactory = $translatorFactory;
    }
    
    /**
     * 
     * @return \App\Controls\ChangePasswordControl
     */
    public function getForum()
    {
        return new \App\Controls\ChangePasswordControl(
            $this->userManager,
            $this->translatorFactory->forumTranslatorFactory(),
            $this->user,
            $this->users
        );
    }
    
    /**
     * 
     * @return \App\Controls\ChangePasswordControl
     */
    public function getAdmin()
    {
        return new \App\Controls\ChangePasswordControl(
            $this->userManager,
            $this->translatorFactory->adminTranslatorFactory(),
            $this->user,
            $this->users
        );
    }    
    
}
