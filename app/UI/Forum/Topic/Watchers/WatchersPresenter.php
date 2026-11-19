<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Watchers;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\TopicWatchService;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;

/**
 * Who watches a topic: only for its moderators and the admin.
 */
class WatchersPresenter extends Presenter
{
    private TopicEntity $topic;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly TopicWatchService $topicWatchService,
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

        if ($user === null || !$this->permissions->isAllowed($user, $topic, ForumPrivilege::MODERATE)) {
            $this->error('You may not see the watchers of this topic.', IResponse::S403_Forbidden);
        }

        $this->topic = $topic;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->topic = $this->topic;
        $this->template->watches = $this->topicWatchService->watchersOf($this->currentUser, $this->topic);
    }
}
