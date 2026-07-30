<?php

namespace App\AdminModule\Presenters;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\SessionEntity;
use App\Model\Repository\SessionRepository;
use App\Presenters\Base\BasePresenter;
use App\Settings\Avatars;
use App\Settings\CacheDir;
use Doctrine\DBAL\Exception as DbalException;
use Nette\DI\Attributes\Inject;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class IndexPresenter extends BasePresenter
{
    const int MAX_LOGGED_IN_USERS_TO_SHOW = 200;

    #[Inject]
    public Avatars $avatar;

    #[Inject]
    public CacheDir $cacheDir;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly SessionRepository $sessionRepository
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
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     * IndexPresenter beforeRender.
     *
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        
        $this->getTemplate()->setTranslator($this->translatorFactory->getAdminTranslator());
    }

    /**
     *
     */
    public function renderDefault(): void
    {
        $count = $this->sessionRepository->findCountOfLoggedUsers();

        $loggedUsers = [];

        if ($count <= self::MAX_LOGGED_IN_USERS_TO_SHOW) {
            $loggedUsers = $this->sessionRepository->findLoggedInUsers();
        }

        $this->getTemplate()->countLogged   = $count;
        $this->getTemplate()->maxLogged     = self::MAX_LOGGED_IN_USERS_TO_SHOW;
        $this->getTemplate()->loggedUsers   = $loggedUsers;
        $this->getTemplate()->avatarDirSize = $this->avatar->getDirSize();
        $this->getTemplate()->avatarCount   = $this->avatar->getImageCount();
        $this->getTemplate()->cacheDirSize  = $this->cacheDir->getDirSize();
    }
    
    /**
     * truncate sessions
     */
    public function actionDeleteSessions(): void
    {
        try {
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE session');

            $this->flashMessage('Sessions were deleted.', 'success');
        } catch (DbalException $exception) {

        }
        
        $this->redirect('Index:default');
    }

    /**
     * logout user
     */
    public function actionLogout(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('User was logged out.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect(':Forum:Index:default');
    }
}
