<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicMoveEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Settings\TopicsSetting;
use DateTimeImmutable;

/**
 * Moving a topic to another forum (slice 6d): a moderator of the forum of the topic (or the admin) moves it to an active
 * forum where he may start topics. Only topic.forum and topic.category change: posts, thanks, polls and reports have no
 * forum of their own (step 6c0). Every move is recorded in topic_move, the old forum shows "moved to ..." for
 * topics.movedNoticeDays days.
 */
class TopicMoveService
{
    public const string ERROR_TARGET = 'forum-topic-move.error.target';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly ForumTreeService $forumTree,
        private readonly TopicsSetting $topicsSetting,
    ) {
    }

    public function mayMove(?UserEntity $user, TopicEntity $topic) : bool
    {
        return $user !== null && $this->permissions->isAllowed($user, $topic->forum, ForumPrivilege::MODERATE);
    }

    /**
     * The forums the topic may be moved to: active, of an active category, not the current one, the user may start topics there.
     *
     * @return list<ForumEntity>
     */
    public function targets(UserEntity $user, TopicEntity $topic) : array
    {
        /** @var list<ForumEntity> $forums */
        $forums = $this->em->getRepository(ForumEntity::class)
            ->createQueryBuilder('_f')
            ->where('_f.active = true')
            ->andWhere('_f.id != :current')
            ->setParameter('current', $topic->forum->id)
            ->orderBy('_f.sortOrder', 'ASC')
            ->addOrderBy('_f.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter($forums, fn (ForumEntity $forum) : bool => $this->isTarget($user, $topic, $forum)));
    }

    public function move(UserEntity $user, TopicEntity $topic, ForumEntity $target) : void
    {
        if (!$this->mayMove($user, $topic)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        $category = $this->forumTree->categoryOf($target);

        if (!$this->isTarget($user, $topic, $target) || $category === null) {
            throw new PostWriteException(self::ERROR_TARGET);
        }

        $move = new TopicMoveEntity();
        $move->topic = $topic;
        $move->fromForum = $topic->forum;
        $move->toForum = $target;
        $move->user = $user;

        $topic->forum = $target;
        $topic->category = $category;
        $topic->updatedAt = new DateTimeImmutable();

        $this->em->wrapInTransaction(function () use ($move) : void {
            $this->em->persist($move);
        });
    }

    /**
     * The recent moves away from the forum (the newest first), for "moved to ..." in the list of its topics; a topic that
     * came back since is left out, a topic moved away more times is there once.
     *
     * @return list<TopicMoveEntity>
     */
    public function recentMovesFrom(ForumEntity $forum) : array
    {
        $days = (int) $this->topicsSetting->get()['movedNoticeDays'];

        /** @var list<TopicMoveEntity> $moves */
        $moves = $this->em->getRepository(TopicMoveEntity::class)
            ->createQueryBuilder('_m')
            ->select('_m', '_t', '_to')
            ->innerJoin('_m.topic', '_t')
            ->innerJoin('_m.toForum', '_to')
            ->where('_m.fromForum = :forum')
            ->andWhere('_t.forum != :forum')
            ->andWhere('_m.createdAt > :since')
            ->setParameter('forum', $forum)
            ->setParameter('since', new DateTimeImmutable('-' . $days . ' days'))
            ->orderBy('_m.createdAt', 'DESC')
            ->addOrderBy('_m.id', 'DESC')
            ->getQuery()
            ->getResult();

        // a topic moved away more times: only its last move
        $last = [];

        foreach ($moves as $move) {
            $last[$move->topic->id] ??= $move;
        }

        return array_values($last);
    }

    private function isTarget(UserEntity $user, TopicEntity $topic, ForumEntity $forum) : bool
    {
        $category = $this->forumTree->categoryOf($forum);

        return $forum->id !== $topic->forum->id
            && $forum->active
            && $category !== null
            && $category->active
            && $this->permissions->isAllowed($user, $forum, ForumPrivilege::TOPIC_ADD);
    }
}
