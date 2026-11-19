<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Reply;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicPaginationService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * A reply to a topic with an optional title (the fast reply without a title is on the topic page).
 */
class ReplyPresenter extends Presenter
{
    private TopicEntity $topic;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly TopicPaginationService $topicPagination,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
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
    }

    public function renderDefault() : void
    {
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

        $form->addSubmit('send', 'forum-topic-reply.form.send');

        $form->onSuccess[] = $this->replyFormSucceeded(...);

        return $form;
    }

    private function replyFormSucceeded(Form $form) : void
    {
        $values = $form->getValues(ReplyValues::class);

        try {
            $post = $this->postWriteService->reply($this->currentUser, $this->topic, $values->text, $values->title, (string) $this->getHttpRequest()->getRemoteAddress());
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-topic-reply.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default#post-' . $post->id, ['uuid' => $this->topic->uuid->toString(), 'page' => $this->topicPagination->pageOf($post)]);
    }
}
