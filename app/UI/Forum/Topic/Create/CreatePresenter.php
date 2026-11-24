<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Create;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\AttachmentService;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\UI\Forum\ForumBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * A new topic in a forum, with its first post.
 */
class CreatePresenter extends ForumBasePresenter
{
    private ForumEntity $forum;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly Translator $translator,
        private readonly AttachmentService $attachmentService,
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
        $this->template->preview ??= null;
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

        $form->addMultiUpload('attachments', 'forum-topic-create.form.attachments.label');

        $form->addSubmit('send', 'forum-topic-create.form.send');
        // shows the text as it will look (Markdown), saves nothing
        $form->addSubmit('preview', 'forum-topic-create.form.preview')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary');

        $form->onSuccess[] = $this->createTopicFormSucceeded(...);

        return $form;
    }

    private function createTopicFormSucceeded(Form $form) : void
    {
        if ($form['preview'] instanceof \Nette\Forms\Controls\SubmitButton && $form['preview']->isSubmittedBy()) {
            $this->template->preview = (string) $form->getUntrustedValues()->text;

            return;
        }

        $values = $form->getValues(CreateTopicValues::class);

        try {
            /** @var list<\Nette\Http\FileUpload> $uploads */
            $uploads = array_values(array_filter((array) $form->getValues('array')['attachments'], static fn (\Nette\Http\FileUpload $upload) : bool => $upload->hasFile()));
            // a wrong attachment stops the whole post
            $this->attachmentService->validate($uploads);
            $topic = $this->postWriteService->createTopic($this->currentUser, $this->forum, $values->name, $values->text, (string) $this->getHttpRequest()->getRemoteAddress());
            $this->attachmentService->store($this->currentUser, $this->postWriteService->firstPostOf($topic), $uploads);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->forumFlash('forum-topic-create.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default', ['uuid' => $topic->uuid->toString()]);
    }
}
