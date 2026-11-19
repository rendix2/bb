<?php declare(strict_types=1);

namespace App\UI\Forum\Forum\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\TopicRepository;
use App\Settings\ForumSettings;
use Nette\Application\UI\Presenter;
use Nette\Utils\Paginator;

/**
 * A forum: its subforums, moderators and one page of its topics.
 */
class ViewPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumSettings $forumSettings,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $uuid, int $page = 1) : void
    {
        $forum = $this->em->getRepository(ForumEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($forum === null || !$forum->active || ($forum->category !== null && !$forum->category->active)) {
            $this->error('Forum was not found.');
        }

        /** @var TopicRepository $topicRepository */
        $topicRepository = $this->em->getRepository(TopicEntity::class);

        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->forumSettings->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($topicRepository->count(['forum' => $forum]));
        $paginator->setPage($page);

        $this->template->forum = $forum;
        $this->template->subforums = $this->em->getRepository(ForumEntity::class)
            ->findBy(['parent' => $forum, 'active' => true], ['sortOrder' => 'ASC']);
        $this->template->topics = $topicRepository->findPageOfForum($forum, $paginator->getOffset(), $paginator->getLength());
        $this->template->paginator = $paginator;
    }

}
