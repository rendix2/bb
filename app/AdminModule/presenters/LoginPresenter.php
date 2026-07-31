<?php

namespace App\AdminModule\Presenters;

use App\Authorization\Authorizator;
use App\Database\EntityManagerDecorator;
use App\Forms\UserLoginForm;
use App\Model\Repository\UserRepository;
use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Translator;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

/**
 * Description of LoginPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class LoginPresenter extends BasePresenter
{
    #[Persistent]
    public string $backlink = '';
    
    private Translator $translator;


    public function __construct(
        private readonly UserRepository $userRepository,

        private readonly UserLoginFormFactory $userLoginFormFactory,

        private readonly EntityManagerDecorator $em,
    )
    {
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::BACK_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->getAdminTranslator();
        $this->getTemplate()->setTranslator($this->translator);
    }

    protected function createComponentAdminLoginForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addText('username', 'Login:');
        $form->addPassword('password', 'Password:');
        $form->addSubmit('send', 'Login');

        $form->onSuccess[] = [$this, 'adminLoginFormSuccess'];
        
        return $form;
    }

    public function adminLoginFormSuccess(Form $form, ArrayHash $values): void
    {
        // check if user is admin
        try {
            $user = $this->getUser();
            
            $user->login(
                $values->user_name,
                $values->user_password
            );
            
            if (!$user->isInRole(Authorizator::ROLES[5])) {
                throw new AuthenticationException('You are not admin.');
            }

            $userEntity = $this->userRepository
                ->findOneBy(
                    [
                        'id' => $user->getId(),
                    ]
                );

            foreach ($userEntity->sessions as $session) {
                $this->em->remove($session);
            }

            $this->em->flush();
            
            $sessionEntity = new \App\Model\Entity\SessionEntity();
            $sessionEntity->key = $this->getSession()->getId();
            $sessionEntity->user = $userEntity;
            $sessionEntity->lastActivity = new \DateTimeImmutable();

            $this->em->persist($sessionEntity);
            $this->em->flush();

            $user->setExpiration('1 hour');
            $this->flashMessage(
                'Successfully admin logged in.',
                self::FLASH_MESSAGE_SUCCESS
            );
            $this->restoreRequest($this->backlink);
            $this->redirect(':Admin:Index:default');
        } catch (AuthenticationException $e) {
            $this->flashMessage(
                $e->getMessage(),
                self::FLASH_MESSAGE_DANGER
            );
        }
    }

    protected function createComponentLoginForm(): UserLoginForm
    {
        return $this->userLoginFormFactory->create();
    }
}
