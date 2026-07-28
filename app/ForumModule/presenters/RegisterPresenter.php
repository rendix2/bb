<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Database\EntityManagerDecorator;
use App\Model\Repository\LanguageRepository;
use App\Models\LanguageManager;
use App\Models\PmManager;
use App\Models\UserFacade;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Localization\ITranslator;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;

/**
 * Description of RegisterPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
class RegisterPresenter extends BasePresenter
{
    /**
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    /**
     * @var PmManager $pmManager
     * @inject
     */
    public PmManager $pmManager;

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
        UserFacade $userFacade,

        private readonly EntityManagerDecorator $em,
        private readonly Passwords              $passwords,
        private readonly LanguageRepository     $languageRepository,
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
        
        $this->translator = $this->translatorFactory->getForumTranslator();
                
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
        $foundUsersByUsernames = $this->em
            ->getRepository(\App\Model\Entity\UserEntity::class)
            ->findBy(
                [
                    'username' => $values->user_name,
                ]
            );
        
        if (count($foundUsersByUsernames)) {
            $form->addError('User name is already taken.');
        }

        $foundUsersByEmails = $this->em
            ->getRepository(\App\Model\Entity\UserEntity::class)
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
        $user = new \App\Model\Entity\UserEntity();

        $useEntity = new \App\Model\Entity\UserEntity();
        $useEntity->username = $values->user_name;
        $useEntity->password = $this->passwords->hash($values->user_password);
        $useEntity->email = $values->user_email;

        $user->setUser_name($values->user_name)
             ->setUser_password($values->user_password)
             ->setUser_email($values->user_email)
             ->setUser_lang_id($values->user_lang_id)
             ->setUser_register_time(time())
             ->setUser_role_id(2)
             ->setUser_activation_key(Random::generate(32));

        $res = $this->userFacade->add($useEntity);

        $this->bbMailer->setSubject($this->translator->translate('welcome_mail_subject'));
        $this->bbMailer->addRecipients([$useEntity->email]);
        $this->bbMailer->setText(
            sprintf(
                $this->translator->translate('welcome_mail_text'),
                $user->user_name,
                $this->link(
                    '//Login:activate',
                    $res,
                    $user->user_activation_key
                )
            )
        );
        $this->bbMailer->send();


        // refresh cache on index page to show this last topic
        $cache = new Cache($this->storage, IndexPresenter::CACHE_NAMESPACE);
        $cache->remove(IndexPresenter::CACHE_KEY_LAST_USER);
        $cache->remove(IndexPresenter::CACHE_KEY_TOTAL_USERS);

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
