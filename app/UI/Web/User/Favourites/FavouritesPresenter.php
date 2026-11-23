<?php declare(strict_types=1);

namespace App\UI\Web\User\Favourites;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Model\Entity\UserEntity;
use App\Model\Service\UserFavouriteService;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Nette\Application\UI\Multiplier;

/**
 * My favourite users (slice 4f): private (decision of 2026-11-19), so there is no uuid in the address - always the own list. Each
 * can be removed (POST + CSRF).
 */
class FavouritesPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly UserFavouriteService $userFavouriteService,
    )
    {
        parent::__construct($em);
    }

    public function actionDefault() : void
    {
        $this->loadViewer();
    }

    public function renderDefault() : void
    {
        $this->getTemplate()->favourites = $this->userFavouriteService->favouritesOf($this->viewer);
    }

    /**
     * Remove one favourite: one form per id of the favourite user (a uuid has dashes, the separator of component names).
     */
    protected function createComponentRemoveForm() : Multiplier
    {
        return new Multiplier(function (string $userId) : IconBootstrapForm {
            $form = $this->buttonForm('web-user-favourites.remove', 'fas fa-star', 'btn btn-outline-danger btn-sm');
            $form->onSuccess[] = function () use ($userId) : void {
                $favourite = $this->em->find(UserEntity::class, (int) $userId);

                if ($favourite === null) {
                    $this->error('User not found');
                }

                $this->userFavouriteService->remove($this->viewer, $favourite);
                $this->flash('web-user-favourites.removed');
                $this->redirect('this');
            };

            return $form;
        });
    }
}
