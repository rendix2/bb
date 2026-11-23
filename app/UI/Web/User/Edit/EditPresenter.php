<?php declare(strict_types=1);

namespace App\UI\Web\User\Edit;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Forms\Renderer\IconBootstrapRenderer;
use App\Forms\UsernameFormControlFactory;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AvatarException;
use App\Model\Service\UserAvatarService;
use App\Settings\Avatars;
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
 * and the username, if users.canChangeUserName allows it (unique case-insensitively, UsernameFormControl); the avatar
 * (its own POST forms with a CSRF token: upload/replace and delete, UserAvatarService).
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
        private readonly UserAvatarService      $userAvatarService,
        private readonly Avatars                $avatars,
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
        $this->getTemplate()->avatar = $userEntity->avatar;
    }

    public function avatarFormSuccess(Form $form) : void
    {
        $values = $form->getValues(AvatarValues::class);
        $userEntity = $this->loggedUser();

        try {
            $this->userAvatarService->change($userEntity, $values->avatar);
        } catch (AvatarException $exception) {
            $form->addError($this->translator->translate($exception->getMessage(), [
                'maxWidth' => $this->avatars->getMaxWidth(),
                'maxHeight' => $this->avatars->getMaxHeight(),
                'maxSize' => (int) round($this->avatars->getMaxFileSize() / 1024 / 1024),
            ]));

            return;
        }

        $this->flashAvatar('web-user-edit.avatar.success');
        $this->redirect('this');
    }

    public function avatarDeleteFormSuccess(Form $form) : void
    {
        $this->userAvatarService->delete($this->loggedUser());

        $this->flashAvatar('web-user-edit.avatar.deleted');
        $this->redirect('this');
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

    protected function createComponentAvatarForm() : IconBootstrapForm
    {
        $form = new IconBootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('web-user-edit.form.protection');

        $maxSize = $this->avatars->getMaxFileSize();
        // the browser check only; UserAvatarService judges the content (type, dimensions, animation) and re-encodes it
        $form->addUpload('avatar', 'web-user-edit.avatar.label')
            ->setRequired('web-user-edit.avatar.required')
            ->addRule(Form::Image, 'web-user-edit.avatar.image')
            ->addRule(Form::MaxFileSize, $this->translator->translate('web-user-edit.avatar.error.size', ['maxSize' => (int) round($maxSize / 1024 / 1024)]), $maxSize)
            ->setHtmlAttribute('accept', 'image/jpeg,image/png,image/gif,image/webp')
            ->setOption('description', $this->translator->translate('web-user-edit.avatar.description', [
                'maxWidth' => $this->avatars->getMaxWidth(),
                'maxHeight' => $this->avatars->getMaxHeight(),
                'maxSize' => (int) round($maxSize / 1024 / 1024),
            ]));

        $form->addSubmit('send', 'web-user-edit.avatar.submit')
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-upload');

        $form->onSuccess[] = [$this, 'avatarFormSuccess'];

        return $form;
    }

    protected function createComponentAvatarDeleteForm() : IconBootstrapForm
    {
        $form = new IconBootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('web-user-edit.form.protection');

        $form->addSubmit('delete', 'web-user-edit.avatar.delete')
            ->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm')
            ->setOption(IconBootstrapRenderer::ICON, 'fas fa-trash');

        $form->onSuccess[] = [$this, 'avatarDeleteFormSuccess'];

        return $form;
    }

    /**
     * The forms are processed before actionDefault() would refuse a guest (a POST does not need the page to have been shown).
     */
    private function loggedUser() : UserEntity
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('user not logged in', IResponse::S403_Forbidden);
        }

        $userEntity = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($userEntity === null) {
            $this->error('user not found');
        }

        return $userEntity;
    }

    private function flashAvatar(string $message) : void
    {
        $flash = new stdClass();
        $flash->place = 'general';
        $flash->type = 'success';
        $flash->message = $this->translator->translate($message);

        $this->flashMessage($flash);
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
