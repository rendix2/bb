<?php declare(strict_types=1);

namespace App\UI\Forum\Pm\Accessory;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Service\PrivateMessageException;
use App\Model\Service\PrivateMessageService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

/**
 * Private messages (slice 5): only for logged-in users (a guest goes to the login and comes back), the layout of the forum.
 */
abstract class PrivateMessagePresenter extends Presenter
{
    protected UserEntity $viewer;

    public function __construct(
        protected readonly EntityManagerDecorator $em,
        protected readonly PrivateMessageService $privateMessages,
        protected readonly Translator $translator,
    ) {
        parent::__construct();
    }

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

    protected function newForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-pm.form.protection');

        return $form;
    }

    /**
     * A form of one button (POST with a CSRF token) for a change of the thread.
     */
    protected function buttonForm(string $caption, string $class = 'btn btn-outline-secondary btn-sm') : BootstrapForm
    {
        $form = $this->newForm();
        $form->addSubmit('send', $caption)
            ->setHtmlAttribute('class', $class);

        return $form;
    }

    /**
     * The error of the service as the error of the form (translated with its parameters, e.g. the unknown username).
     */
    protected function formError(Form $form, PrivateMessageException $exception) : void
    {
        $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);
    }
}
