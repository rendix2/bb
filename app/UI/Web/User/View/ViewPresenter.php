<?php declare(strict_types=1);

namespace App\UI\Web\User\View;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Model\Repository\PostRepository;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\UserFavouriteException;
use App\Model\Service\UserFavouriteService;
use App\Model\Service\UserProfileService;
use App\Model\Service\UserRankService;
use App\UI\Web\User\Accessory\ProfilePresenter;

/**
 * class ViewPresenter
 *
 * bb: the profile of the forum (rank, counts, registration, moderated forums), only for logged-in users (ProfilePresenter);
 * the name and the surname see only the user himself and the admin. Without uuid it is the own profile. On another profile the viewer
 * adds / removes him as a favourite (POST + CSRF, slice 4f; the old code did it by a GET link).
 *
 * @package App\UI\Web\User\View
 */
class ViewPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly PostRepository $postRepository,
        private readonly UserProfileService $userProfileService,
        private readonly UserRankService $userRankService,
        private readonly UserFavouriteService $userFavouriteService,
    )
    {
        parent::__construct($em);
    }

    public function actionDefault(?string $uuid = null) : void
    {
        $this->loadProfile($uuid);
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
        // is the shown user a favourite of the viewer (the old code compared the user with himself)
        $this->getTemplate()->isFavourite = $this->userFavouriteService->isFavourite($this->viewer, $this->userEntity);
    }

    /**
     * Add / remove the shown user as a favourite of the viewer (actionDefault has loaded both before the signal).
     */
    protected function createComponentFavouriteForm() : IconBootstrapForm
    {
        $isFavourite = $this->userFavouriteService->isFavourite($this->viewer, $this->userEntity);
        $form = $isFavourite
            ? $this->buttonForm('web-user-view.favourite.remove', 'fas fa-star')
            : $this->buttonForm('web-user-view.favourite.add', 'far fa-star');

        $form->onSuccess[] = function () use ($isFavourite) : void {
            try {
                if ($isFavourite) {
                    $this->userFavouriteService->remove($this->viewer, $this->userEntity);
                    $this->flash('web-user-view.favourite.removed');
                } else {
                    $this->userFavouriteService->add($this->viewer, $this->userEntity);
                    $this->flash('web-user-view.favourite.added');
                }
            } catch (UserFavouriteException $exception) {
                $this->flash($exception->getMessage(), 'danger');
            }

            $this->redirect('this');
        };

        return $form;
    }
}
