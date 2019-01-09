<?php

namespace App\Services;

use App\Forms\UserDeleteAvatarForm;
use App\Models\UsersManager;
use App\Settings\Avatars;
use Nette\Security\User;

/**
 * Description of DeleteAvatarFactory
 *
 * @author rendix2
 * @package App\Services
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
     * DeleteAvatarFactory constructor.
     *
     * @param UsersManager      $usersManager
     * @param Avatars           $avatars
     * @param User              $user
     * @param TranslatorFactory $translatorFactory
     */
    public function __construct(
        UsersManager      $usersManager,
        Avatars           $avatars,
        User              $user,
        TranslatorFactory $translatorFactory
    ) {
        $this->userManager       = $usersManager;
        $this->avatars           = $avatars;
        $this->user              = $user;
        $this->translatorFactory = $translatorFactory;
    }
    
    /**
     * DeleteAvatarFactory destructor.
     */
    public function __destruct()
    {
        $this->userManager       = null;
        $this->avatars           = null;
        $this->user              = null;
        $this->translatorFactory = null;
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
            $this->translatorFactory->createForumTranslatorFactory()
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
            $this->translatorFactory->createAdminTranslatorFactory()
        );
    }
}
