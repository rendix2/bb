<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Create;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * A new topic in a forum, with its first post.
 */
class CreatePresenter extends Presenter
{
    private ForumEntity $forum;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $forum = $this->em->getRepository(ForumEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($forum === null || !$forum->active || ($forum->category !== null && !$forum->category->active)) {
            $this->error('Forum was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($user === null || !$this->permissions->isAllowed($user, $forum, ForumPrivilege::TOPIC_ADD)) {
            $this->error('You may not start a topic in this forum.', IResponse::S403_Forbidden);
        }

        $this->forum = $forum;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->forum = $this->forum;
    }

    protected function createComponentCreateTopicForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-create.form.protection');

        $form->addText('name', 'forum-topic-create.form.name.label')
            ->setRequired('forum-topic-create.form.name.required')
            ->setMaxLength(1024);

        $form->addTextArea('text', 'forum-topic-create.form.text.label', rows: 12)
            ->setRequired('forum-topic-create.form.text.required');

        $form->addSubmit('send', 'forum-topic-create.form.send');

        $form->onSuccess[] = $this->createTopicFormSucceeded(...);

        return $form;
    }

    private function createTopicFormSucceeded(Form $form) : void
    {
        $values = $form->getValues(CreateTopicValues::class);

        try {
            $topic = $this->postWriteService->createTopic($this->currentUser, $this->forum, $values->name, $values->text, (string) $this->getHttpRequest()->getRemoteAddress());
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-topic-create.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default', ['uuid' => $topic->uuid->toString()]);
    }
}
