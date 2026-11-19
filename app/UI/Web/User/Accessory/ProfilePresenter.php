<?php declare(strict_types=1);

namespace App\UI\Web\User\Accessory;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use Nette\Application\UI\Presenter;

/**
 * A page about one user (profile, his posts, his topics): only for logged-in users (decision of 2026-11-19; a guest goes to the
 * login and comes back), the user by uuid, without uuid the own one (the identity has no uuid: found by the id).
 */
abstract class ProfilePresenter extends Presenter
{
    protected UserEntity $userEntity;

    protected UserEntity $viewer;

    public function __construct(
        protected readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct();
    }

    protected function loadProfile(?string $uuid) : void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default', ['backlink' => $this->storeRequest()]);
        }

        $viewer = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($viewer === null) {
            $this->error('User not found');
        }

        $this->viewer = $viewer;

        if ($uuid === null) {
            $this->redirect('this', ['uuid' => $viewer->uuid->toString()]);
        }

        $userEntity = $this->em->getRepository(UserEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($userEntity === null) {
            $this->error('User not found');
        }

        $this->userEntity = $userEntity;
    }
}
