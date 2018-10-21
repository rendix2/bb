<?php

namespace App\Services;

use App\Forms\UserChangePasswordForm;
use App\Models\UsersManager;
use App\Settings\Users;
use Nette\Security\User;

/**
 * Description of ChangePasswordFactory
 *
 * @author rendix2
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
    public function __construct(
        UsersManager $userManager,
        TranslatorFactory $translatorFactory,
        User $user,
        Users $users
    ) {
        $this->userManager       = $userManager;
        $this->user              = $user;
        $this->users             = $users;
        $this->translatorFactory = $translatorFactory;
    }
    
    public function __destruct()
    {
        $this->userManager       = null;
        $this->user              = null;
        $this->users             = null;
        $this->translatorFactory = null;
    }

    /**
     *
     * @return UserChangePasswordForm
     */
    public function getForum()
    {
        return new UserChangePasswordForm(
            $this->userManager,
            $this->translatorFactory->forumTranslatorFactory(),
            $this->user,
            $this->users
        );
    }

    /**
     *
     * @return UserChangePasswordForm
     */
    public function getAdmin()
    {
        return new UserChangePasswordForm(
            $this->userManager,
            $this->translatorFactory->adminTranslatorFactory(),
            $this->user,
            $this->users
        );
    }
}
