<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Reply;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\AttachmentService;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicPaginationService;
use App\UI\Forum\ForumBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * A reply to a topic with an optional title (the fast reply without a title is on the topic page).
 */
class ReplyPresenter extends ForumBasePresenter
{
    private TopicEntity $topic;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly TopicPaginationService $topicPagination,
        private readonly Translator $translator,
        private readonly AttachmentService $attachmentService,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid, ?string $quote = null) : void
    {
        $topic = $this->em->getRepository(TopicEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($topic === null || !$topic->forum->active || !$topic->category->active) {
            $this->error('Topic was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($user === null || !$this->permissions->isAllowed($user, $topic, ForumPrivilege::REPLY)) {
            $this->error('You may not reply to this topic.', IResponse::S403_Forbidden);
        }

        $this->topic = $topic;
        $this->currentUser = $user;

        if ($quote !== null && !$this['replyForm']->isSubmitted()) {
            // a post of THIS topic only; the quote is Markdown the user may still change
            $quoted = $this->em->getRepository(PostEntity::class)->findOneBy(['uuid' => $quote, 'topic' => $topic]);

            if ($quoted !== null) {
                $lines = array_map(static fn (string $line) : string => '> ' . $line, explode("\n", $quoted->text));
                $this['replyForm']->setDefaults(['text' => '> **' . $quoted->user->username . "** napsal:\n>\n" . implode("\n", $lines) . "\n\n"]);
            }
        }
    }

    public function renderDefault() : void
    {
        $this->template->preview ??= null;
        $this->template->topic = $this->topic;
    }

    protected function createComponentReplyForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-reply.form.protection');

        $form->addText('title', 'forum-topic-reply.form.title.label')
            ->setMaxLength(1024);

        $form->addTextArea('text', 'forum-topic-reply.form.text.label', rows: 12)
            ->setRequired('forum-topic-reply.form.text.required');

        $form->addMultiUpload('attachments', 'forum-topic-reply.form.attachments.label');

        $form->addSubmit('send', 'forum-topic-reply.form.send');
        // shows the text as it will look (Markdown), saves nothing
        $form->addSubmit('preview', 'forum-topic-reply.form.preview')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary');

        $form->onSuccess[] = $this->replyFormSucceeded(...);

        return $form;
    }

    private function replyFormSucceeded(Form $form) : void
    {
        if ($form['preview'] instanceof \Nette\Forms\Controls\SubmitButton && $form['preview']->isSubmittedBy()) {
            $this->template->preview = (string) $form->getUntrustedValues()->text;

            return;
        }

        $values = $form->getValues(ReplyValues::class);

        try {
            /** @var list<\Nette\Http\FileUpload> $uploads */
            $uploads = array_values(array_filter((array) $form->getValues('array')['attachments'], static fn (\Nette\Http\FileUpload $upload) : bool => $upload->hasFile()));
            // a wrong attachment stops the whole post
            $this->attachmentService->validate($uploads);
            $post = $this->postWriteService->reply($this->currentUser, $this->topic, $values->text, $values->title, (string) $this->getHttpRequest()->getRemoteAddress());
            $this->attachmentService->store($this->currentUser, $post, $uploads);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->forumFlash('forum-topic-reply.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default#post-' . $post->id, ['uuid' => $this->topic->uuid->toString(), 'page' => $this->topicPagination->pageOf($post)]);
    }
}
