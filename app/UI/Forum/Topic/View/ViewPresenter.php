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
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicPaginationService;
use App\Model\Service\UserRankService;
use App\Settings\TopicsSetting;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;

/**
 * A topic: one page of its posts with their authors (post count, rank, signature).
 */
class ViewPresenter extends Presenter
{
    private TopicEntity $topic;

    private ?UserEntity $currentUser = null;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly TopicsSetting $topicsSetting,
        private readonly UserRankService $userRankService,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly TopicPaginationService $topicPagination,
        private readonly Translator $translator,
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

        if ($this->getUser()->isLoggedIn()) {
            $this->currentUser = $this->em->find(UserEntity::class, $this->getUser()->getId());
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
        /** @var PostRepository $postRepository */
        $postRepository = $this->em->getRepository(PostEntity::class);

        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->topicsSetting->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($postRepository->count(['topic' => $this->topic]));
        $paginator->setPage($page);

        $posts = $postRepository->findPageOfTopic($this->topic, $paginator->getOffset(), $paginator->getLength());

        $authorIds = array_values(array_unique(array_map(static fn (PostEntity $post) : int => $post->user->id, $posts)));
        $postCounts = $postRepository->countByUsers($authorIds);

        $ranks = [];

        foreach ($posts as $post) {
            $ranks[$post->user->id] ??= $this->userRankService->rankOf($post->user, $postCounts[$post->user->id]);
        }

        $this->template->topic = $this->topic;
        $this->template->posts = $posts;
        $this->template->postCounts = $postCounts;
        $this->template->ranks = $ranks;
        $this->template->paginator = $paginator;
        $this->template->canReply = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::REPLY);
        $this->template->canFastReply = $this->permissions->isAllowed($this->currentUser, $this->topic, ForumPrivilege::FAST_REPLY);
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

        $this->flashMessage('forum-topic-view.form.success', 'success');
        $this->redirect('this#post-' . $post->id, ['page' => $this->topicPagination->pageOf($post)]);
    }

}
