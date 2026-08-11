<?php

namespace App\Services;

use App\Database\EntityManagerDecorator;
use App\Forms\UserDeleteAvatarForm;
use App\Model\Repository\UserRepository;
use Nette\Localization\Translator;
use Nette\Security\User;

class DeleteAvatarFactory
{

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator             $translator,

        private readonly UserRepository $userRepository,

        private readonly AvatarService $avatarService,

        private readonly User              $user
    ) {
    }

    /**
     *
     * @return UserDeleteAvatarForm
     */
    public function getForum()
    {
        return new UserDeleteAvatarForm(
            $this->em,
            $this->user,
            $this->translator,
            $this->userRepository,
            $this->avatarService,
        );
    }
    
    /**
     *
     * @return UserDeleteAvatarForm
     */
    public function getAdmin()
    {
        return new UserDeleteAvatarForm(
            $this->em,
            $this->user,
            $this->translator,
            $this->userRepository,
            $this->avatarService,
        );
    }
}
