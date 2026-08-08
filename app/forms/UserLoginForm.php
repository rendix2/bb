<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\SessionEntity;
use App\Model\Entity\UserEntity;
use App\Presenters\Base\BasePresenter;
use DateTimeImmutable;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Localization\Translator;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of UserLoginForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserLoginForm extends Control
{
    /**
     * @var string $backlink
     * @persistent
     */
    public string $backlink = '';

    
    /**
     *
     * @var User $user
     */
    private User $user;
    
    /**
     * @var Session $session
     */
    private Session $session;

    /**
     * UserLoginForm constructor.
     *
     */
    public function __construct(
        private readonly Translator $translator,
        User              $user,
        Session           $session,
        private readonly EntityManagerDecorator $em,
    ) {
        $this->user              = $user;
        $this->session           = $session;
    }

    /**
     *  UserLoginForm render.
     */
    public function render(): void
    {
        $this['loginForm']->render();
    }


    protected function createComponentLoginForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send', 'Log in');
        $form->onSuccess[] = [
            $this,
            'loginForumSuccess'
        ];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function loginForumSuccess(Form $form, ArrayHash $values): void
    {
        try {
            $user = $this->user;

            $user->login(
                $values->user_name,
                $values->user_password
            );

            $userEntity = $this->em
                ->getRepository(UserEntity::class)
                ->findOneBy(
                    [
                        'id' => $user->getId(),
                    ]
                );

            $sessionEntity = new SessionEntity();
            $sessionEntity->user = $userEntity;
            $sessionEntity->key = $this->session->getId();
            $sessionEntity->lastActivity = new DateTimeImmutable();

            $sessions = $this->em
                ->getRepository(SessionEntity::class)
                ->findOneBy(
                    [
                        'key' => $this->session->getId(),
                    ]
                );

            foreach ($sessions as $session) {
                $this->em->remove($session);
            }

            $this->em->persist($sessionEntity);
            $this->em->flush();

            $user->setExpiration('1 hour');

            $this->flashMessage('Successfully logged in.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->redrawControl('flashes');

            $this->getPresenter()->restoreRequest($this->backlink);
            $this->getPresenter()->redirect('Index:default');
        } catch (AuthenticationException $e) {
            $this->getPresenter()->flashMessage(
                $e->getMessage(),
                BasePresenter::FLASH_MESSAGE_DANGER
            );
        }
    }
}
