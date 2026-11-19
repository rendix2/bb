<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\PostRepository;
use App\Model\Service\UserRankService;
use App\Settings\TopicsSetting;
use Nette\Application\UI\Presenter;
use Nette\Utils\Paginator;

/**
 * A topic: one page of its posts with their authors (post count, rank, signature).
 */
class ViewPresenter extends Presenter
{
    private TopicEntity $topic;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly TopicsSetting $topicsSetting,
        private readonly UserRankService $userRankService,
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
    }

}
