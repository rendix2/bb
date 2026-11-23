<?php declare(strict_types=1);

namespace App\UI\Web\User\Accessory;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Forms\Renderer\IconBootstrapRenderer;
use App\Model\Entity\UserEntity;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use stdClass;

/**
 * A page about one user (profile, his posts, his topics): only for logged-in users (decision of 2026-11-19; a guest goes to the
 * login and comes back), the user by uuid, without uuid the own one (the identity has no uuid: found by the id).
 */
abstract class ProfilePresenter extends Presenter
{
    protected UserEntity $userEntity;

    protected UserEntity $viewer;

    protected Translator $translator;

    public function __construct(
        protected readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct();
    }

    public function injectProfileTranslator(Translator $translator) : void
    {
        $this->translator = $translator;
    }

    /**
     * Only the logged-in viewer (a guest goes to the login and comes back): the pages of the own account (favourites, watched topics).
     */
    protected function loadViewer() : void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default', ['backlink' => $this->storeRequest()]);
        }

        $viewer = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($viewer === null) {
            $this->error('User not found');
        }

        $this->viewer = $viewer;
    }

    protected function loadProfile(?string $uuid) : void
    {
        $this->loadViewer();

        if ($uuid === null) {
            $this->redirect('this', ['uuid' => $this->viewer->uuid->toString()]);
        }

        $userEntity = $this->em->getRepository(UserEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($userEntity === null) {
            $this->error('User not found');
        }

        $this->userEntity = $userEntity;
    }

    /**
     * A form of one button (POST with a CSRF token) for a change: add / remove a favourite, stop watching.
     */
    protected function buttonForm(string $caption, string $icon, string $class = 'btn btn-outline-secondary btn-sm') : IconBootstrapForm
    {
        $form = new IconBootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('web-user-view.form.protection');

        $form->addSubmit('send', $caption)
            ->setHtmlAttribute('class', $class)
            ->setOption(IconBootstrapRenderer::ICON, $icon);

        return $form;
    }

    protected function flash(string $message, string $type = 'success') : void
    {
        $flash = new stdClass();
        $flash->place = 'general';
        $flash->type = $type;
        $flash->message = $this->translator->translate($message);

        $this->flashMessage($flash);
    }
}
