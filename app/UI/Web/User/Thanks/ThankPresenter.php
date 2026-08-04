<?php

namespace App\UI\Web\User\Thanks;

use App\Model\Repository\UserRepository;
use Nette\Application\UI\Presenter;

class ThankPresenter extends Presenter
{
    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {
    }

    public function actionDefault($user_id): void
    {
        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        if ($userEntity === null){
            $this->error('User was not found');
        }

        $thanks = $userEntity->thanks;

        $this->getTemplate()->thanks = $thanks->fetchAll();
    }

}