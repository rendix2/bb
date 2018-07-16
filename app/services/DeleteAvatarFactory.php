<?php

namespace App\Services;

use App\Models\UsersManager;
use App\Settings\Avatars;
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
     * @return \App\Controls\DeleteAvatarControl
     */
    public function getForum()
    {
        return new \App\Forms\DeleteAvatarForm(
            $this->userManager,
            $this->avatars,
            $this->user,
            $this->translatorFactory->forumTranslatorFactory()
        );
    }
    
    /**
     * 
     * @return \App\Controls\DeleteAvatarControl
     */
    public function getAdmin()
    {
        return new \App\Forms\DeleteAvatarForm(
            $this->userManager,
            $this->avatars,
            $this->user,
            $this->translatorFactory->adminTranslatorFactory()
        );   
    }
}
