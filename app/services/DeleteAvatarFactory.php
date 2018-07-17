<?php

namespace App\Services;

use App\Models\UsersManager;
use App\Settings\Avatars;
use App\Forms\UserDeleteAvatarForm;
use Nette\Security\User;

/**
 * Description of DeleteAvatarFactory
 *
 * @author rendi
 */
class DeleteAvatarFactory
{

    /**
     *
     * @var UsersManager $userManager
     */
    private $userManager;
    
    /**
     *
     * @var Avatars $avatars
     */
    private $avatars;
    
    /**
     *
     * @var User $user
     */
    private $user;
    
    /**
     * @var TranslatorFactory $translatorFactory
     */
    private $translatorFactory;
    
    /**
     * 
     * @param UsersManager $userManager
     * @param Avatars $avatars
     * @param User $user
     * @param TranslatorFactory $translatorFactory
     */
    public function __construct(UsersManager $userManager, Avatars $avatars, User $user, TranslatorFactory $translatorFactory) {
        $this->userManager       = $userManager;
        $this->avatars           = $avatars;
        $this->user              = $user;
        $this->translatorFactory = $translatorFactory;
    }

    /**
     * 
     * @return UserDeleteAvatarForm
     */
    public function getForum()
    {
        return new UserDeleteAvatarForm(
            $this->userManager,
            $this->avatars,
            $this->user,
            $this->translatorFactory->forumTranslatorFactory()
        );
    }
    
    /**
     * 
     * @return UserDeleteAvatarForm
     */
    public function getAdmin()
    {
        return new UserDeleteAvatarForm(
            $this->userManager,
            $this->avatars,
            $this->user,
            $this->translatorFactory->adminTranslatorFactory()
        );   
    }
}
