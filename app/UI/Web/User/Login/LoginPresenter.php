<?php declare(strict_types=1);

namespace App\UI\Web\User\Login;

use App\Core\AutoLoginService;
use App\Core\UsernameAndPasswordAuthenticator;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Service\GeoIpService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;
use Tracy\ILogger;

class LoginPresenter extends Presenter
{
    public function __construct(
        private readonly Translator                       $translator,
        private readonly EntityManagerDecorator           $em,
        private readonly UsernameAndPasswordAuthenticator $usernameAndPasswordAuthenticator,
        private readonly GeoIpService                     $geoIpService,
        private readonly AutoLoginService                 $autoLoginService,
    )
    {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate('web-user-login.alreadyLoggedIn'), 'danger');
            $this->redrawControl('flashes');
        }
    }

    public function createComponentLoginForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('Please try again.');

        $form->addText('username', 'web-user-login.form.username.label')
            ->setRequired('web-user-login.form.username.required')
            ->setMaxLength(512);

        $form->addPassword('password', 'web-user-login.form.password.label')
            ->setRequired('web-user-login.form.password.required');

        $form->addCheckbox('stayLoggedIn', 'web-user-login.form.stayLoggedIn');

        $submitButton = $form->addSubmit('login', 'web-user-login.form.submit.name')
            ->setHtmlAttribute('class', 'mt-2');

        if ($this->getUser()->isLoggedIn()) {
            $submitButton
                ->setDisabled();
        }

        $form->onSuccess[] = [$this, 'loginFormSuccess'];

        return $form;
    }

    public function loginFormSuccess(Form $form) : void
    {
        $values = $form->getValues(LoginValues::class);

        try {
            $this->getUser()->setAuthenticator($this->usernameAndPasswordAuthenticator);
            $this->getUser()->login($values->username, $values->password);
            // without "stay logged in" the session really ends soon; with it the autoLogin cookie keeps the user logged in.
            // Not longer than session.expiration in config/common.neon (14 days), Nette refuses that.
            $this->getUser()->setExpiration($values->stayLoggedIn ? '14 days' : '2 hours');
        } catch (AuthenticationException $exception) {
            // the authenticators throw translation keys, never a text for the user
            $this->flashMessage($this->translator->translate($exception->getMessage()), 'danger');
            $this->redrawControl('flashes');

            return;
        }

        if ($values->stayLoggedIn) {
            $this->remember();
        }

        $this->flashMessage($this->translator->translate('web-user-login.form.submit.success'), 'success');
        $this->redirect(':Web:Home:default');
    }

    /**
     * "Stay logged in": a token, its cookie survives closing the browser (AutoLoginService::remember()). A failure does not undo
     * the login, the user is only told.
     */
    private function remember() : void
    {
        $userEntity = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($userEntity === null) {
            return;
        }

        $ip = $this->getHttpRequest()->getRemoteAddress();
        $countryCode = $ip === null ? null : $this->geoIpService->getInfo($ip)['country'];

        try {
            $this->autoLoginService->remember($this, $userEntity, $ip ?? '0.0.0.0', $countryCode);
        } catch (DbalException $exception) {
            Debugger::log($exception, ILogger::EXCEPTION);
            $this->flashMessage($this->translator->translate('web-user-login.form.submit.autoLoginError'), 'danger');
        }
    }
}
