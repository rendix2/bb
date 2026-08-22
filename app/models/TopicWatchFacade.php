<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Utils;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicWatchFacade
 *
 * @author rendix2
 * @package App\Models
 */
class TopicWatchFacade
{

    public function __construct(
        UsersManager      $usersManager,
        private readonly EntityManagerDecorator $em,

        private readonly PostRepository  $postRepository,

        private readonly TopicWatchRepository $topicWatchRepository,
    ) {
    }

    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity): void
    {
        foreach ($topicEntity->watches as $watch) {
            $this->em->remove($watch);
        }

        $this->em->flush();
    }

    public function deleteByPost(\App\Model\Entity\PostEntity $post): void
    {
        $postCount = $this->postRepository->getCountOfUsersByTopicId($post->topic->id);

        foreach ($postCount as $ps) {
            // check if user has there only one post so we can delete his topic watching
            // else he can still want to watch this topic
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $topicWatchEntity = $this->topicWatchRepository->findOneBy(
                    [
                        'topic' => $post->topic,
                        'user'  => $post->user,
                    ]
                );

                if ($topicWatchEntity) {
                    $this->em->remove($topicWatchEntity);
                    $this->em->flush();
                }
            }
        }
    }
}
