<?php

namespace App\Services;

use App\Database\EntityManagerDecorator;
use App\Forms\UserChangePasswordForm;
use App\Model\Repository\UserRepository;
use App\Settings\Users;
use Nette\Localization\Translator;
use Nette\Security\Passwords;
use Nette\Security\User;

/**
 * Description of ChangePasswordFactory
 *
 * @author rendix2
 * @package App\Services
 */
class ChangePasswordFactory
{
    public function __construct(
        private readonly Translator $translator,

        private readonly EntityManagerDecorator $em,

        private readonly UserRepository $userRepository,

        private readonly Passwords $passwords,

        private readonly User  $user,
        private readonly Users $users
    ) {
    }

    /**
     *
     * @return UserChangePasswordForm
     */
    public function getForum(): UserChangePasswordForm
    {
        return new UserChangePasswordForm(
            $this->translator,
            $this->user,
            $this->users,
            $this->em,
            $this->userRepository,
            $this->passwords,
        );
    }

    /**
     *
     * @return UserChangePasswordForm
     */
    public function getAdmin(): UserChangePasswordForm
    {
        return new UserChangePasswordForm(
            $this->translator,
            $this->user,
            $this->users,
            $this->em,
            $this->userRepository,
            $this->passwords,
        );
    }
}
