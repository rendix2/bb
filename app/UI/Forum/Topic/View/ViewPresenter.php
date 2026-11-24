<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\PostRepository;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Entity\PollEntity;
use App\Model\Service\AttachmentService;
use App\Model\Service\PollService;
use App\Model\Service\PostHistoryService;
use App\Model\Service\PostWriteService;
use App\Model\Service\ReportService;
use App\Model\Service\ThankService;
use App\Model\Service\TopicWatchService;
use App\Model\Service\TopicPaginationService;
use App\Model\Service\UserRankService;
use App\Settings\TopicsSetting;
use App\UI\Forum\Accessory\ForumJumpFormFactory;
use App\UI\Forum\ForumBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;

/**
 * A topic: one page of its posts with their authors (post count, rank, signature).
 */
class ViewPresenter extends ForumBasePresenter
{
    private TopicEntity $topic;

    private ?UserEntity $currentUser = null;

    private ?PollEntity $poll = null;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly PostRepository $postRepository,
        private readonly TopicsSetting $topicsSetting,
        private readonly UserRankService $userRankService,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly TopicPaginationService $topicPagination,
        private readonly Translator $translator,
        private readonly ThankService $thankService,
        private readonly TopicWatchService $topicWatchService,
        private readonly PollService $pollService,
        private readonly AttachmentService $attachmentService,
        private readonly ReportService $reportService,
        private readonly PostHistoryService $postHistory,
        private readonly ForumJumpFormFactory $forumJumpFormFactory,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid, int $page = 1) : void
    {
        $topic = $this->em->getRepository(TopicEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($topic === null || !$topic->forum->active || !$topic->category->active) {
            $this->error('Topic was not found.');
        }

        $this->topic = $topic;
        $this->poll = $this->pollService->pollOf($topic);

        if ($this->getUser()->isLoggedIn()) {
            $this->currentUser = $this->em->find(UserEntity::class, $this->getUser()->getId());
        }

        if ($this->currentUser !== null) {
            // a watcher who opened the topic may be notified about the next reply again
            $this->topicWatchService->visited($this->currentUser, $topic);
        }

        if ((bool) $this->topicsSetting->get()['logViews']) {
            // one UPDATE, not read-modify-write: concurrent views are all counted
            $this->em->getRepository(TopicEntity::class)
                ->createQueryBuilder('_t')
                ->update()
                ->set('_t.viewCount', '_t.viewCount + 1')
                ->where('_t.id = :id')
                ->setParameter('id', $topic->id)
                ->getQuery()
                ->execute();
        }
    }

    public function renderDefault(string $uuid, int $page = 1) : void
    {
        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->topicsSetting->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($this->postRepository->count(['topic' => $this->topic]));
        $paginator->setPage($page);

        $posts = $this->postRepository->findPageOfTopic($this->topic, $paginator->getOffset(), $paginator->getLength());

        $authorIds = array_values(array_unique(array_map(static fn (PostEntity $post) : int => $post->user->id, $posts)));
        $postCounts = $this->postRepository->countByUsers($authorIds);

        $ranks = [];

        foreach ($posts as $post) {
            $ranks[$post->user->id] ??= $this->userRankService->rankOf($post->user, $postCounts[$post->user->id]);
        }

        $this->template->topic = $this->topic;
        $this->template->posts = $posts;
        $this->template->postCounts = $postCounts;
        $this->template->ranks = $ranks;
        $this->template->paginator = $paginator;
        $canEdit = [];
        $canDelete = [];
        $canReport = [];
        $canViewHistory = [];
        $firstPostId = $paginator->isFirst() && $posts !== [] ? $posts[0]->id : null;

        foreach ($posts as $post) {
            $canEdit[$post->id] = $this->permissions->isAllowed($this->currentUser, $post, ForumPrivilege::EDIT);
            // the first post goes only with its topic (Forum:Topic:Delete)
            $canDelete[$post->id] = $post->id !== $firstPostId && $this->permissions->isAllowed($this->currentUser, $post, ForumPrivilege::DELETE);
            $canReport[$post->id] = $this->reportService->mayReport($this->currentUser, $post);
            $canViewHistory[$post->id] = $post->updatedAt !== null && $this->postHistory->mayView($this->currentUser, $post);
        }

        $this->template->canEdit = $canEdit;
        $this->template->canDelete = $canDelete;
        $this->template->canReport = $canReport;
        $this->template->canViewHistory = $canViewHistory;
        $this->template->canThank = $this->currentUser !== null && $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::THANK);
        $this->template->postThanks = $this->thankService->postThanks($posts, $this->currentUser);
        $this->template->topicThankCount = $this->thankService->topicThankCount($this->topic);
        $this->template->hasThankedTopic = $this->thankService->hasThankedTopic($this->currentUser, $this->topic);
        $this->template->isWatching = $this->topicWatchService->isWatching($this->currentUser, $this->topic);
        $this->template->attachments = $this->attachmentService->attachmentsOf($posts);
        $this->template->poll = $this->poll;
        $this->template->pollResults = $this->poll !== null ? $this->pollService->results($this->poll) : [];
        $this->template->pollOpen = $this->poll !== null && $this->pollService->isOpen($this->poll);
        $this->template->canVote = $this->poll !== null && $this->pollService->mayVote($this->currentUser, $this->poll);
        $this->template->canAddPoll = $this->poll === null && $this->pollService->mayAddPoll($this->currentUser, $this->topic);
        $this->template->canModerate = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::MODERATE);
        $this->template->canDeleteTopic = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::DELETE);
        $this->template->canReply = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::REPLY);
        $this->template->canFastReply = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::FAST_REPLY);
    }

    /**
     * Jump to another forum (GET, changes nothing).
     */
    protected function createComponentForumJumpForm() : BootstrapForm
    {
        return $this->forumJumpFormFactory->create($this->topic->forum);
    }

    protected function createComponentFastReplyForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-view.form.protection');

        $form->addTextArea('text', 'forum-topic-view.form.text.label', rows: 5)
            ->setRequired('forum-topic-view.form.text.required');

        $form->addSubmit('send', 'forum-topic-view.form.send');

        $form->onSuccess[] = $this->fastReplyFormSucceeded(...);

        return $form;
    }

    /**
     * Lock / unlock the topic (moderator): POST with CSRF protection, the permission checked when written.
     */
    protected function createComponentLockTopicForm() : BootstrapForm
    {
        $form = $this->buttonForm($this->topic->isLocked ? 'forum-topic-view.unlockTopic' : 'forum-topic-view.lockTopic');
        $form->onSuccess[] = function () : void {
            $this->moderate(fn (UserEntity $user) => $this->postWriteService->setTopicLocked($user, $this->topic, !$this->topic->isLocked));
            $this->forumFlash($this->topic->isLocked ? 'forum-topic-view.topicLockedDone' : 'forum-topic-view.topicUnlockedDone', 'success');
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * Lock / unlock one post of the page (moderator): one form per post id.
     */
    protected function createComponentLockPostForm() : Multiplier
    {
        return new Multiplier(function (string $postId) : BootstrapForm {
            $post = $this->em->getRepository(PostEntity::class)->findOneBy(['id' => (int) $postId, 'topic' => $this->topic]);

            if ($post === null) {
                $this->error('Post was not found.');
            }

            $form = $this->buttonForm($post->isLocked ? 'forum-topic-view.unlockPost' : 'forum-topic-view.lockPost');
            $form->onSuccess[] = function () use ($post) : void {
                $this->moderate(fn (UserEntity $user) => $this->postWriteService->setPostLocked($user, $post, !$post->isLocked));
                $this->forumFlash($post->isLocked ? 'forum-topic-view.postLockedDone' : 'forum-topic-view.postUnlockedDone', 'success');
                $this->redirect('this#post-' . $post->id);
            };

            return $form;
        });
    }

    /**
     * Thank the topic (POST + CSRF; permission, own topic and "once" checked when written).
     */
    protected function createComponentThankTopicForm() : BootstrapForm
    {
        $form = $this->buttonForm('forum-topic-view.thankTopic');
        $form->onSuccess[] = function () : void {
            $this->userAction(fn (UserEntity $user) => $this->thankService->thankTopic($user, $this->topic, (string) $this->getHttpRequest()->getRemoteAddress()), 'forum-topic-view.thankDone');
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * Thank one post of the page: one form per post id.
     */
    protected function createComponentThankPostForm() : Multiplier
    {
        return new Multiplier(function (string $postId) : BootstrapForm {
            $post = $this->em->getRepository(PostEntity::class)->findOneBy(['id' => (int) $postId, 'topic' => $this->topic]);

            if ($post === null) {
                $this->error('Post was not found.');
            }

            $form = $this->buttonForm('forum-topic-view.thankPost');
            $form->onSuccess[] = function () use ($post) : void {
                $this->userAction(fn (UserEntity $user) => $this->thankService->thankPost($user, $post, (string) $this->getHttpRequest()->getRemoteAddress()), 'forum-topic-view.thankDone');
                $this->redirect('this#post-' . $post->id);
            };

            return $form;
        });
    }

    /**
     * Watch / stop watching the topic (logged-in users).
     */
    protected function createComponentWatchForm() : BootstrapForm
    {
        $watching = $this->topicWatchService->isWatching($this->currentUser, $this->topic);
        $form = $this->buttonForm($watching ? 'forum-topic-view.unwatch' : 'forum-topic-view.watch');
        $form->onSuccess[] = function () use ($watching) : void {
            $this->userAction(
                fn (UserEntity $user) => $watching
                    ? $this->topicWatchService->unwatch($user, $this->topic)
                    : $this->topicWatchService->watch($user, $this->topic),
                $watching ? 'forum-topic-view.unwatchDone' : 'forum-topic-view.watchDone',
            );
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * An action of a logged-in user: the result (or the error) as a flash message.
     *
     * @param callable(UserEntity): void $action
     */
    private function userAction(callable $action, string $successMessage) : void
    {
        if ($this->currentUser === null) {
            $this->error('Not logged in.', 403);
        }

        try {
            $action($this->currentUser);
            $this->forumFlash($successMessage, 'success');
        } catch (PostWriteException $exception) {
            $this->forumFlash($exception->getMessage(), 'danger');
        }
    }

    /**
     * Vote in the poll of the topic (one answer, once; checked when written).
     */
    protected function createComponentVoteForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-view.form.protection');

        $answers = [];

        foreach ($this->poll !== null ? $this->pollService->results($this->poll) : [] as $row) {
            $answers[$row['answer']->id] = $row['answer']->text;
        }

        $form->addRadioList('answer', null, $answers)
            ->setTranslator(null)
            ->setRequired('forum-topic-view.pollChoose');
        $form->addSubmit('vote', 'forum-topic-view.pollVote');

        $form->onSuccess[] = function (Form $form) : void {
            $poll = $this->poll ?? $this->error('The topic has no poll.');
            $answerId = (int) $form->getValues('array')['answer'];
            $this->userAction(fn (UserEntity $user) => $this->pollService->vote($user, $poll, $answerId), 'forum-topic-view.pollVoted');
            $this->redirect('this');
        };

        return $form;
    }

    private function buttonForm(string $caption) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-view.form.protection');
        $form->addSubmit('toggle', $caption)
            ->setHtmlAttribute('class', 'btn btn-outline-secondary btn-sm');

        return $form;
    }

    /**
     * @param callable(UserEntity): void $action
     */
    private function moderate(callable $action) : void
    {
        if ($this->currentUser === null) {
            $this->error('Not logged in.', 403);
        }

        try {
            $action($this->currentUser);
        } catch (PostWriteException) {
            $this->error('You may not moderate this topic.', 403);
        }
    }

    private function fastReplyFormSucceeded(Form $form) : void
    {
        $values = $form->getValues(FastReplyValues::class);

        // checked again when written (PostWriteService): a POST does not need the form to have been shown
        if ($this->currentUser === null) {
            $this->error('Not logged in.', 403);
        }

        try {
            $post = $this->postWriteService->reply($this->currentUser, $this->topic, $values->text, null, (string) $this->getHttpRequest()->getRemoteAddress(), fastReply: true);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->forumFlash('forum-topic-view.form.success', 'success');
        $this->redirect('this#post-' . $post->id, ['page' => $this->topicPagination->pageOf($post)]);
    }

}
