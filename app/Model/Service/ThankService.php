<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;

/**
 * Thanks for a topic (post = null) or for one post: THANK permission, never for one's own topic / post, once per user
 * (also guarded by the unique indexes of the table thank).
 */
class ThankService
{
    public const string ERROR_OWN = 'forum-thank.error.own';
    public const string ERROR_ALREADY = 'forum-thank.error.already';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
    ) {
    }

    public function thankTopic(UserEntity $user, TopicEntity $topic, string $ipAddress) : void
    {
        $this->check($user, $topic, $topic->user);

        if ($this->hasThankedTopic($user, $topic)) {
            throw new PostWriteException(self::ERROR_ALREADY);
        }

        $this->save($user, $topic, null, $ipAddress);
    }

    public function thankPost(UserEntity $user, PostEntity $post, string $ipAddress) : void
    {
        $this->check($user, $post->topic, $post->user);

        if ($this->em->getRepository(ThankEntity::class)->count(['user' => $user, 'post' => $post]) > 0) {
            throw new PostWriteException(self::ERROR_ALREADY);
        }

        $this->save($user, $post->topic, $post, $ipAddress);
    }

    public function hasThankedTopic(?UserEntity $user, TopicEntity $topic) : bool
    {
        return $user !== null && $this->em->getRepository(ThankEntity::class)->count(['user' => $user, 'topic' => $topic, 'post' => null]) > 0;
    }

    public function topicThankCount(TopicEntity $topic) : int
    {
        return $this->em->getRepository(ThankEntity::class)->count(['topic' => $topic, 'post' => null]);
    }

    /**
     * All thanks in the topic with their users: those of the topic first, then by the posts in their order.
     *
     * @return list<ThankEntity>
     */
    public function thanksOf(TopicEntity $topic) : array
    {
        /** @var list<ThankEntity> */
        return $this->em->getRepository(ThankEntity::class)
            ->createQueryBuilder('_t')
            ->addSelect('_u', '_p', 'CASE WHEN _p.id IS NULL THEN 0 ELSE 1 END AS HIDDEN topicFirst')
            ->join('_t.user', '_u')
            ->leftJoin('_t.post', '_p')
            ->where('_t.topic = :topic')
            ->setParameter('topic', $topic)
            ->orderBy('topicFirst', 'ASC')
            ->addOrderBy('_p.createdAt', 'ASC')
            ->addOrderBy('_p.id', 'ASC')
            ->addOrderBy('_t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Number of thanks of each post and the posts the user has thanked (one query each for a page of posts).
     *
     * @param list<PostEntity> $posts
     * @return array{counts: array<int, int>, thankedByUser: array<int, true>}
     */
    public function postThanks(array $posts, ?UserEntity $user) : array
    {
        $ids = array_map(static fn (PostEntity $post) : int => $post->id, $posts);
        $counts = array_fill_keys($ids, 0);
        $thanked = [];

        if ($ids === []) {
            return ['counts' => $counts, 'thankedByUser' => $thanked];
        }

        $rows = $this->em->getRepository(ThankEntity::class)
            ->createQueryBuilder('_t')
            ->select('IDENTITY(_t.post) AS postId', 'COUNT(_t.id) AS thankCount')
            ->where('_t.post IN (:posts)')
            ->setParameter('posts', $ids)
            ->groupBy('_t.post')
            ->getQuery()
            ->getArrayResult();

        foreach ($rows as $row) {
            $counts[(int) $row['postId']] = (int) $row['thankCount'];
        }

        if ($user !== null) {
            $rows = $this->em->getRepository(ThankEntity::class)
                ->createQueryBuilder('_t')
                ->select('IDENTITY(_t.post) AS postId')
                ->where('_t.post IN (:posts)')
                ->andWhere('_t.user = :user')
                ->setParameter('posts', $ids)
                ->setParameter('user', $user)
                ->getQuery()
                ->getArrayResult();

            foreach ($rows as $row) {
                $thanked[(int) $row['postId']] = true;
            }
        }

        return ['counts' => $counts, 'thankedByUser' => $thanked];
    }

    private function check(UserEntity $user, TopicEntity $topic, UserEntity $author) : void
    {
        if (!$this->permissions->isAllowed($user, $topic, ForumPrivilege::THANK)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        if ($author->id === $user->id) {
            throw new PostWriteException(self::ERROR_OWN);
        }
    }

    private function save(UserEntity $user, TopicEntity $topic, ?PostEntity $post, string $ipAddress) : void
    {
        $thank = new ThankEntity();
        $thank->user = $user;
        $thank->topic = $topic;
        $thank->forum = $topic->forum;
        $thank->category = $topic->category;
        $thank->post = $post;
        $thank->ipAddress = $ipAddress;

        $this->em->persist($thank);
        $this->em->flush();
    }
}
