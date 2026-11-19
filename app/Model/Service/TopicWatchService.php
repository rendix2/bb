<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;

/**
 * A logged-in user watches a topic: he gets a mail about a new reply (queued, one until his next visit of the topic).
 */
class TopicWatchService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
    }

    public function isWatching(?UserEntity $user, TopicEntity $topic) : bool
    {
        return $user !== null && $this->find($user, $topic) !== null;
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
