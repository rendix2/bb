<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\SessionEntity;
use App\Model\Repository\SessionRepository;
use App\Presenters\Base\BasePresenter;
use App\Settings\Avatars;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 */
class IndexPresenter extends BasePresenter
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
        private readonly EntityManagerDecorator $em,
    )
    {
    }

    /**
     *
     * @param mixed $element
     */
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

    /**
     * IndexPresenter beforeRender.
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        
        $this->getTemplate()->setTranslator($this->translatorFactory->getAdminTranslator());
    }

    public function renderDefault(): void
    {
        /**
         * @var SessionRepository $sessionRepository
         */
        $sessionRepository = $this->em
            ->getRepository(SessionEntity::class);

        $count = $sessionRepository->getCountOfLoggedUsers();

        $loggedUsers = [];

        if ($count <= self::MAX_LOGGED_IN_USERS_TO_SHOW) {
            $loggedUsers = $sessionRepository->getLoggedInUsers();
        }

        $this->getTemplate()->countLogged = $count;
        $this->getTemplate()->maxLogged   = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->getTemplate()->loggedUsers = $loggedUsers;
        $this->getTemplate()->dirSize     = $this->avatar->getDirSize();
        $this->getTemplate()->avatarCount = $this->avatar->getImageCount();
    }
}
