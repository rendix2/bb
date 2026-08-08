<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Model\Repository\SessionRepository;
use App\Settings\Avatars;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 */
class IndexPresenter extends Presenter
{
    /**
     * @var int
     */
    const int MAX_LOGGED_IN_USERS_TO_SHOW = 200;

    /**
     * @var Avatars $avatar
     * @inject
     */
    public Avatars $avatar;

    public function __construct(
        private readonly Translator $translator,


        private readonly SessionRepository $sessionRepository,
    )
    {
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if (!$user->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    public function renderDefault(): void
    {
        $sessionRepository = $this->sessionRepository->findCountOfLoggedUsers();

        $loggedUsers = [];

        if ($count <= self::MAX_LOGGED_IN_USERS_TO_SHOW) {
            $loggedUsers = $sessionRepository->findLoggedInUsers();
        }

        $this->getTemplate()->countLogged = $count;
        $this->getTemplate()->maxLogged   = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->getTemplate()->loggedUsers = $loggedUsers;
        $this->getTemplate()->dirSize     = $this->avatar->getDirSize();
        $this->getTemplate()->avatarCount = $this->avatar->getImageCount();
    }
}
