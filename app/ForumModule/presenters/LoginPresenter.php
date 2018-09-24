<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Forms\UserLoginForm;
use App\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Description of LoginPresenter
 *
 * @author rendix2
 */
class LoginPresenter extends BasePresenter
{
    /**
     * @var string $backlink
     * @persistent
     */
    public $backlink = '';
    
    /**
     *
     * @var UserLoginFormFactory $userLoginForm
     * @inject
     */
    public $userLoginForm;

    /**
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;

    /**
     * @var BBMailer $bbMailer
     * @inject
     */
    public $bbMailer;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * LoginPresenter constructor.
     */
    public function startup()
    {
        parent::startup();

        $this->translator = $this->translatorFactory->forumTranslatorFactory();
    }

    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }
    
    /**
     * before render method
     * sets translator
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translatorFactory->forumTranslatorFactory());
    }

    /**
     * @param int    $user_id
     * @param string $key
     */
    public function actionActivate($user_id, $key)
    {
        $ok = true;

        if (!$user_id || !$key) {
            $this->flashMessage('Parameters are not set.', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->flashMessage('You are logged in!', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        // after register
        $can = $this->usersManager->canBeActivated($user_id, $key);

        if (!$can) {
            $this->flashMessage('You cannot be activated.', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        if ($ok) {
            $this->usersManager
                ->update($user_id, ArrayHash::from([
                    'user_active' => 1,
                    'user_activation_key' => null
                ]));
            $this->flashMessage('You have been activated.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Login:default');
        } else {
            $this->redirect('Index:default');
        }
    }

    public function renderReactivate()
    {
        // :)
    }
    
    /**
     *
     * @return UserLoginForm
     */
    public function createComponentLoginForm()
    {
        return $this->userLoginForm->create();
    }

    protected function createComponentReactivateForm()
    {
        $form = BootstrapForm::create();

        $form->addEmail('user_email', 'User email:');
        $form->addSubmit('submit', 'Send');
        $form->onSuccess[] = [$this, 'reactivateFormSuccess'];

        return $form;
    }

    public function reactivateFormSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->usersManager->getByEmail($values->user_email);

        if ($user) {
            $user = $user[0];

            if (!$user->user_active) {
                $key = Manager::getRandomString();

                $this->usersManager->update($user->user_id, ArrayHash::from(['user_activation_key' => $key]));

                $this->bbMailer->addRecipients([$values->user_email]);
                $this->bbMailer->setSubject($this->translator->translate('welcome_mail_subject'));
                $this->bbMailer->addRecipients([$values->user_email]);
                $this->bbMailer->setText(
                    sprintf(
                        $this->translator->translate('welcome_mail_text'),
                        $user->user_name,
                        $this->link(
                            '//Login:activate',
                            $user->user_id,
                            $key
                        )
                    )
                );
                $this->bbMailer->send();
            }
        }

        $this->flashMessage('OK', self::FLASH_MESSAGE_SUCCESS);
    }
}
