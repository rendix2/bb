<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Thanks;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Service\ThankService;
use App\Model\Service\TopicPaginationService;
use Nette\Application\UI\Presenter;

/**
 * Who thanked in a topic: for the topic itself and for each of its posts.
 */
class ThanksPresenter extends Presenter
{
    private TopicEntity $topic;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ThankService $thankService,
        private readonly TopicPaginationService $topicPagination,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $topic = $this->em->getRepository(TopicEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($topic === null || !$topic->forum->active || !$topic->category->active) {
            $this->error('Topic was not found.');
        }

        $this->topic = $topic;
    }

    public function renderDefault() : void
    {
        $topicThanks = [];
        /** @var array<int, array{post: PostEntity, page: int, thanks: list<ThankEntity>}> $postThanks */
        $postThanks = [];

        foreach ($this->thankService->thanksOf($this->topic) as $thank) {
            if ($thank->post === null) {
                $topicThanks[] = $thank;

                continue;
            }

            $postThanks[$thank->post->id] ??= ['post' => $thank->post, 'page' => $this->topicPagination->pageOf($thank->post), 'thanks' => []];
            $postThanks[$thank->post->id]['thanks'][] = $thank;
        }

        $this->template->topic = $this->topic;
        $this->template->topicThanks = $topicThanks;
        $this->template->postThanks = $postThanks;
    }
}
