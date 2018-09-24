<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\LanguagesManager;
use App\Models\Manager;
use App\Models\PmManager;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;

/**
 * Description of RegisterPresenter
 *
 * @author rendix2
 */
class RegisterPresenter extends BasePresenter
{
    /**
     * @var ITranslator $translator
     */
    private $translator;
    
    /**
     * @var LanguagesManager $languageManager
     */
    private $languageManager;
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * @var PmManager $pmManager
     * @inject
     */
    public $pmManager;

    /**
     * RegisterPresenter constructor.
     *
     * @param LanguagesManager $languageManger
     * @param UsersManager     $usersManager
     */
    public function __construct(LanguagesManager $languageManger, UsersManager $usersManager)
    {
        parent::__construct();
        
        $this->languageManager = $languageManger;
        $this->usersManager    = $usersManager;
    }
    
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
                
        $this->template->setTranslator($this->translator);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentRegisterUser()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->translator);

        $form->addText('user_name', 'User name:')
            ->setRequired(true);
        $form->addPassword('user_password', 'User password:')
            ->setRequired(true);
        $form->addPassword('user_password2', 'User password for check:')
            ->setRequired(true);
        $form->addEmail('user_email', 'User email:')
            ->setRequired(true);
        $form->addSelect('user_lang_id', 'User lang:', $this->languageManager->getAllPairsCached('lang_name'));
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
    public function registerOnValidate(Form $form, ArrayHash $values)
    {
        $user_name = $this->usersManager->getByName($values->user_name);
        
        if ($user_name) {
            $form->addError('User name is already taken.');
        }
        
        $user_email = $this->usersManager->getByEmail($values->user_email);
        
        if ($user_email) {
            $form->addError('User email is already taken.');
        }
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function registerUserSuccess(Form $form, ArrayHash $values)
    {
        unset($values->user_password2);
        $values->user_password       = Passwords::hash($values->user_password);
        $values->user_register_time  = time();
        $values->user_role_id        = 1;
        $values->user_activation_key = Manager::getRandomString();

        $res = $this->usersManager->add(ArrayHash::from($values));

        $welcome_pm_data = [
            'pm_user_id_from' => 1,
            'pm_user_id_to'   => $res,
            'pm_subject'      => $this->translator->translate('welcome_pm_subject'),
            'pm_text'         => sprintf($this->translator->translate('welcome_pm_text'), $values->user_name),
            'pm_status'       => 'sent',
            'pm_time_sent'    => time()
        ];

        $this->pmManager->add(ArrayHash::from($welcome_pm_data));
        
        if ($res) {
            $this->flashMessage('User was added.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('User was not added.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('Login:default');
    }

    /**
     *
     */
    public function renderDefault()
    {
    }
}
