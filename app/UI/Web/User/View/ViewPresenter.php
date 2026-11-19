<?php declare(strict_types=1);

namespace App\UI\Web\User\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Repository\PostRepository;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\UserProfileService;
use App\Model\Service\UserRankService;
use Nette\Application\UI\Presenter;

/**
 * class ViewPresenter
 *
 * bb: the profile of the forum (rank, counts, registration, moderated forums), only for logged-in users (a guest goes to the
 * login and comes back); the name and the surname see only the user himself and the admin. Without uuid it is the own profile.
 *
 * @package App\UI\Web\User\View
 */
class ViewPresenter extends Presenter
{
    private UserEntity $userEntity;

    private UserEntity $viewer;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly PostRepository $postRepository,
        private readonly UserProfileService $userProfileService,
        private readonly UserRankService $userRankService,
    )
    {
        parent::__construct();
    }

    public function actionDefault(?string $uuid = null) : void
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

        $userRepository = $this->em->getRepository(UserEntity::class);

        /**
         * @var ?UserEntity $userEntity
         */
        $userEntity = $userRepository->findOneByUuid($uuid);

        if ($userEntity === null) {
            $this->error('User not found');
        }

        $this->userEntity = $userEntity;
    }

    public function renderDefault(?string $uuid = null) : void
    {
        $allPosts = $this->postRepository->countByUsers([$this->userEntity->id])[$this->userEntity->id];

        $isAdmin = false;

        foreach ($this->viewer->roles as $role) {
            $isAdmin = $isAdmin || $role->name === ForumPrivilege::ROLE_ADMIN;
        }

        $this->getTemplate()->userEntity = $this->userEntity;
        $this->getTemplate()->isOwn = $this->userEntity->id === $this->viewer->id;
        // the real name is private: the user himself and the admin (decision of 2026-11-19)
        $this->getTemplate()->showRealName = $this->userEntity->id === $this->viewer->id || $isAdmin;
        // the rank follows all his posts (as at his posts in a topic), the counts only what the viewer may read
        $this->getTemplate()->rank = $this->userRankService->rankOf($this->userEntity, $allPosts);
        $this->getTemplate()->counts = $this->userProfileService->counts($this->userEntity, $this->viewer);
        $this->getTemplate()->moderatedForums = $this->userProfileService->moderatedForums($this->userEntity, $this->viewer);
    }
}
