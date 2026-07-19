<?php

namespace App\Presenters\Base;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\SessionEntity;
use App\Models\SessionManager;
use DateTimeImmutable;
use Nette\DI\Attributes\Inject;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of AuthenticatedPresenter
 *
 * @author rendix2
 * @package App\Presenters\Base
 */
abstract class AuthenticatedPresenter extends BasePresenter
{
    #[Inject]
    public EntityManagerDecorator $aem;

    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if ($user->loggedIn) {
            $sessions = $this->aem
                ->getRepository(SessionEntity::class)
                ->findBy(
                    [
                        'user' => $user->id,
                        'key' => $this->getSession()->getId(),
                    ]
                );

            foreach ($sessions as $session) {
                $now = new DateTimeImmutable();
                $threshold = $now->modify('-1 minute');

                if ($session->lastActivity < $threshold) {
                    $session->lastActivity = $now;
                }
            }

            $this->aem->flush();
        } else {
            if ($user->logoutReason === User::LogoutInactivity) {
                $sessions = $this->aem
                    ->getRepository(SessionEntity::class)
                    ->createQueryBuilder('_s')

                    ->where('_s.user = :user OR _s.key = :key')
                    ->setParameter('user', $user->id)
                    ->setParameter('key', $this->getSession()->getId())

                    ->getQuery()
                    ->getResult();

                foreach ($sessions as $session) {
                    $this->aem->remove($session);
                }

                $this->aem->flush();
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }

            if ($this->getName() !== 'Forum:Index') {
                $sessions = $this->aem
                    ->getRepository(SessionEntity::class)
                    ->findBy(
                        [
                            'key' => $this->getSession()->getId()
                        ]
                    );

                foreach ($sessions as $session) {
                    $this->aem->remove($session);
                }

                $this->aem->flush();
                $this->redirect(':Web:User:Login:default', ['loginForm-backlink' => $this->storeRequest()]);
            }
        }
    }
}
