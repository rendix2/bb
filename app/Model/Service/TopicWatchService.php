<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumVisibilityService;
use App\Model\Security\ForumPrivilege;

/**
 * A logged-in user watches a topic: he gets a mail about a new reply (queued, one until his next visit of the topic).
 */
class TopicWatchService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly ForumVisibilityService $visibility,
    ) {
    }

    /**
     * The topics the user watches (the page "My watched topics"), the newest watch first, each with whether he may still read it
     * (active forum readable for him and active category, as Forum:Topic:View requires). An unreadable one is listed only so that
     * the user can stop watching it – the page must not show its name or forum.
     *
     * @return list<array{watch: TopicWatchEntity, readable: bool}>
     */
    public function watchedBy(UserEntity $user) : array
    {
        $forumIds = $this->visibility->visibleForumIds($user);

        /** @var list<TopicWatchEntity> $watches */
        $watches = $this->em->getRepository(TopicWatchEntity::class)
            ->createQueryBuilder('_w')
            ->select('_w', '_t', '_f', '_tc')
            ->innerJoin('_w.topic', '_t')
            ->innerJoin('_t.forum', '_f')
            ->innerJoin('_t.category', '_tc')
            ->where('_w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('_w.createdAt', 'DESC')
            ->addOrderBy('_t.id', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (TopicWatchEntity $watch) : array => [
                'watch' => $watch,
                'readable' => in_array($watch->topic->forum->id, $forumIds, true) && $watch->topic->category->active,
            ],
            $watches,
        );
    }

    public function isWatching(?UserEntity $user, TopicEntity $topic) : bool
    {
        return $user !== null && $this->find($user, $topic) !== null;
    }

    /**
     * The watchers of the topic, only for a moderator (MODERATE) or the admin.
     *
     * @return list<TopicWatchEntity>
     */
    public function watchersOf(?UserEntity $viewer, TopicEntity $topic) : array
    {
        if (!$this->permissions->isAllowed($viewer, $topic, ForumPrivilege::MODERATE)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        /** @var list<TopicWatchEntity> */
        return $this->em->getRepository(TopicWatchEntity::class)
            ->createQueryBuilder('_w')
            ->addSelect('_u')
            ->join('_w.user', '_u')
            ->where('_w.topic = :topic')
            ->setParameter('topic', $topic)
            ->orderBy('_u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function watch(UserEntity $user, TopicEntity $topic) : void
    {
        if ($this->find($user, $topic) !== null) {
            return;
        }

        $watch = new TopicWatchEntity();
        $watch->user = $user;
        $watch->topic = $topic;
        $this->em->persist($watch);
        $this->em->flush();
    }

    public function unwatch(UserEntity $user, TopicEntity $topic) : void
    {
        $watch = $this->find($user, $topic);

        if ($watch !== null) {
            $this->em->remove($watch);
            $this->em->flush();
        }
    }

    /**
     * The user opened the topic: the next reply may notify him again.
     */
    public function visited(UserEntity $user, TopicEntity $topic) : void
    {
        $this->em->getRepository(TopicWatchEntity::class)
            ->createQueryBuilder('_w')
            ->update()
            ->set('_w.isNotified', ':notNotified')
            ->where('_w.user = :user')
            ->andWhere('_w.topic = :topic')
            ->andWhere('_w.isNotified = true')
            ->setParameter('notNotified', false)
            ->setParameter('user', $user)
            ->setParameter('topic', $topic)
            ->getQuery()
            ->execute();
    }

    private function find(UserEntity $user, TopicEntity $topic) : ?TopicWatchEntity
    {
        return $this->em->getRepository(TopicWatchEntity::class)->findOneBy(['user' => $user, 'topic' => $topic]);
    }
}
