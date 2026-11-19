<?php declare(strict_types=1);

namespace App\UI\Web\User\Edit;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Forms\Renderer\IconBootstrapRenderer;
use App\Forms\UsernameFormControlFactory;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Settings\Users;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use DateTimeImmutable;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;
use Nette\Security\SimpleIdentity;
use stdClass;

/**
 * class EditPresenter
 *
 * bb: besides the name of web-project also the settings of the forum: language, signature (Markdown, users.signatureMaxLength)
 * and the username, if users.canChangeUserName allows it (unique case-insensitively, UsernameFormControl).
 *
 * @package App\UI\Web\User\Edit
 */
class EditPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator             $translator,
        private readonly Users                  $users,
        private readonly UsernameFormControlFactory $usernameFormControlFactory,
    )
    {
        parent::__construct();
    }

    public function actionDefault() : void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('user not logged in', IResponse::S403_Forbidden);
        }

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $this->getUser()->getId(),
                ]
            );

        if ($userEntity === null) {
            $this->error('user not found');
        }

        $defaults = [
            'name' => $userEntity->name,
            'surname' => $userEntity->surname,
            'language' => $userEntity->language->id,
            'signature' => $userEntity->signature,
        ];

        if ($this->canChangeUsername()) {
            $defaults['username'] = $userEntity->username;
        }

        $this['editForm']->setDefaults($defaults);
        // the preview of the saved signature, as under the posts
        $this->getTemplate()->signature = $userEntity->signature;
    }

    public function editFormSuccess(Form $form) : void
    {
        $values = $form->getValues(EditUserProfileValues::class);

        // the form is processed before actionDefault() would refuse a guest (a POST does not need the page to have been shown)
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('user not logged in', IResponse::S403_Forbidden);
        }

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $this->getUser()->getId(),
                ]
            );

        if ($userEntity === null) {
            $this->error('user not found');
        }

        $language = $this->em->find(LanguageEntity::class, $values->language);

        if ($language === null) {
            $form->addError('web-user-edit.form.language.unknown');

            return;
        }

        $userEntity->name = $values->name;
        $userEntity->surname = $values->surname;
        $userEntity->language = $language;
        $userEntity->signature = trim($values->signature) === '' ? null : $values->signature;

        // the control is only in the form when the configuration allows the change
        if ($this->canChangeUsername() && $values->username !== null) {
            $userEntity->username = $values->username;
        }

        $userEntity->updatedAt = new DateTimeImmutable();

        try {
            $this->em->persist($userEntity);
            $this->em->flush();

            // the forum layout shows the username of the identity
            $identity = $this->getUser()->getIdentity();

            if ($identity instanceof SimpleIdentity) {
                $identity->username = $userEntity->username;
            }

            $this->getTemplate()->signature = $userEntity->signature;

            $flash = new stdClass();
            $flash->place = 'general';
            $flash->type = 'success';
            $flash->message = $this->translator->translate('web-user-edit.form.submit.success');

            $this->flashMessage($flash);
            $this->redrawControl('flashes');
        } catch (DbalException $exception) {
            bdump($exception);
            $flash = new stdClass();
            $flash->place = 'general';
            $flash->type = 'danger';
            $flash->message = $this->translator->translate('messages.operationFailed');

            $this->flashMessage($flash);
            $this->redrawControl('flashes');
        }
    }

    protected function createComponentEditForm() : IconBootstrapForm
    {
        $form = new IconBootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        // bb: the CSRF token (web-project relies on the same-origin check of Nette only)
        $form->addProtection('web-user-edit.form.protection');

        if ($this->canChangeUsername()) {
            $minLength = (int) $this->users->get()['minUserNameLength'];
            $usernameControl = $this->usernameFormControlFactory->create($this->translator->translate('admin-user-edit.form.username.label'));
            $usernameControl->setExcludeUserId($this->getUser()->getId());
            $usernameControl->addRule(
                Form::MinLength,
                $this->translator->translate('web-user-edit.form.username.minLength', ['minChars' => $minLength]),
                $minLength,
            );
            $form->addComponent($usernameControl, 'username');
        }

        $form->addText('name', 'admin-user-edit.form.name.label')
            ->setRequired('admin-user-edit.form.name.required')
            ->setMaxLength(512)
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-id-card');

        $form->addText('surname', 'admin-user-edit.form.surname.label')
            ->setRequired('admin-user-edit.form.surname.required')
            ->setMaxLength(512)
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-id-card');

        $languages = [];

        foreach ($this->em->getRepository(LanguageEntity::class)->findBy([], ['name' => 'ASC']) as $language) {
            $languages[$language->id] = $language->name;
        }

        // the names of the languages are not translation keys
        $form->addSelect('language', $this->translator->translate('web-user-edit.form.language.label'), $languages)
            ->setTranslator(null)
            ->setRequired($this->translator->translate('web-user-edit.form.language.required'))
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-language');

        $maxLength = $this->signatureMaxLength();
        $form->addTextArea('signature', 'web-user-edit.form.signature.label', rows: 4)
            ->addRule(Form::MaxLength, $this->translator->translate('web-user-edit.form.signature.maxLength', ['maxChars' => $maxLength]), $maxLength)
            ->setOption('description', $this->translator->translate('web-user-edit.form.signature.description', ['maxChars' => $maxLength]));

        $form->addSubmit('send', 'web-user-edit.form.submit.label')
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-save');

        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }

    private function canChangeUsername() : bool
    {
        return (bool) $this->users->get()['canChangeUserName'];
    }

    private function signatureMaxLength() : int
    {
        return max(1, (int) $this->users->get()['signatureMaxLength']);
    }
}
