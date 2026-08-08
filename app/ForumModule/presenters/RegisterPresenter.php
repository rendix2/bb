<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Model\Entity\UserActivationEntity;
use App\Model\Repository\LanguageRepository;
use App\Model\Repository\UserRepository;
use App\Models\UserFacade;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Caching\IStorage;
use Nette\Localization\Translator;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;

/**
 * Description of RegisterPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
class RegisterPresenter extends Presenter
{
    /**
     * @var BBMailer $bbMailer
     * @inject
     */
    public BBMailer $bbMailer;

    /**
     * @var IStorage $storage
     * @inject
     */
    public IStorage $storage;

    /**
     * @var UserFacade $userFacade
     */
    private UserFacade $userFacade;

    public function __construct(
        private readonly Translator $translator,

        UserFacade $userFacade,

        private readonly Passwords $passwords,

        private readonly LanguageRepository $languageRepository,
        private readonly UserRepository     $userRepository,
    )
    {
        parent::__construct();

        $this->userFacade      = $userFacade;
    }

    /**
     * RegisterPresenter startup.
     */
    public function startup()
    {
        parent::startup();
                
        $this->getTemplate()->setTranslator($this->translator);
    }

    protected function createComponentRegisterUser(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addText('user_name', 'User name:')
            ->setRequired(true);
        $form->addPassword('user_password', 'User password:')
            ->setRequired(true);
        $form->addPassword('user_password2', 'User password for check:')
            ->setRequired(true);
        $form->addEmail('user_email', 'User email:')
            ->setRequired(true);
        $form->addSelect('user_lang_id', 'User lang:', $this->languageRepository->findPairs());
        $form->addReCaptcha('user_captcha', 'User captcha:', 'Please prove you are not robot.');
        $form->addSubmit('send', 'User register');
        
        $form->onValidate[] = [$this, 'registerOnValidate'];
        $form->onSuccess[]  = [$this, 'registerUserSuccess'];
        
        return $form;
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function registerOnValidate(Form $form, ArrayHash $values): void
    {
        $foundUsersByUsernames = $this->userRepository
            ->findBy(
                [
                    'username' => $values->user_name,
                ]
            );
        
        if (count($foundUsersByUsernames)) {
            $form->addError('User name is already taken.');
        }

        $foundUsersByEmails = $this->userRepository
            ->findBy(
                [
                    'email' => $values->user_email,
                ]
            );
        
        if ($foundUsersByEmails) {
            $form->addError('User email is already taken.');
        }
    }

    public function registerUserSuccess(Form $form, ArrayHash $values): void
    {
        $userEntity = new \App\Model\Entity\UserEntity();
        $userEntity->username = $values->user_name;
        $userEntity->password = $this->passwords->hash($values->user_password);
        $userEntity->email = $values->user_email;

        $userActivationEntity = new UserActivationEntity();
        $userActivationEntity->user = $userEntity;
        $userActivationEntity->activationKey = Random::generate(128);

        $userEntity->addUserActivationEntity($userActivationEntity);

        $this->userFacade->add($userEntity);

        $this->bbMailer->setSubject($this->translator->translate('welcome_mail_subject'));
        $this->bbMailer->addRecipients([$userEntity->email]);
        $this->bbMailer->setText(
            sprintf(
                $this->translator->translate('welcome_mail_text'),
                $userEntity->username,
                $this->link(
                    '//Login:activate',
                    $userActivationEntity->activationKey
                )
            )
        );

        $this->bbMailer->send();

        if ($res) {
            $this->flashMessage('User was added.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('User was not added.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('Login:default');
    }

    public function renderDefault(): void
    {
    }
}
