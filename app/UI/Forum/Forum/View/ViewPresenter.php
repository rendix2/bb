<?php declare(strict_types=1);

namespace App\UI\Forum\Forum\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Repository\TopicRepository;
use App\Settings\ForumSettings;
use App\UI\Forum\Accessory\ForumJumpFormFactory;
use Contributte\FormsBootstrap\BootstrapForm;
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
        private readonly ForumPermissionService $permissions,
        private readonly ForumJumpFormFactory $forumJumpFormFactory,
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

        $currentUser = $this->getUser()->isLoggedIn() ? $this->em->find(UserEntity::class, $this->getUser()->getId()) : null;
        $this->template->canAddTopic = $this->permissions->isAllowed($currentUser, $forum, ForumPrivilege::TOPIC_ADD);
        $this['forumJumpForm-forum']->setDefaultValue($forum->uuid->toString());
    }

    /**
     * Jump to another forum (GET, changes nothing).
     */
    protected function createComponentForumJumpForm() : BootstrapForm
    {
        return $this->forumJumpFormFactory->create();
    }

}
