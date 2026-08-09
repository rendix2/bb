<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\TopicRepository;
use Nette\Utils\ArrayHash;

/**
 * Description of ForumFacade
 *
 * @author rendix2
 * @package App\Models
 */
class ForumFacade
{

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,

        private readonly TopicFacade   $topicFacade,
    ) {
    }

    /**
     * @param ForumEntity[] $forums
     * @param int $forum_parent_id
     *
     * @return array
     */
    public function createForums(array $forums, int $forum_parent_id): array
    {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->parent->id === $forum_parent_id) {
                $result[$forum->id] = $forum;
                $result[$forum->id]['childs'] = $this->createForums(
                    $forums,
                    $forum->id
                );
            }
        }

        return $result;
    }


    public function delete(ForumEntity $forumEntity): void
    {
        $forums = $this->forumRepository
            ->findBy(
                [
                    'parent' => $forumEntity,
                ]
            );

        foreach ($forums as $forum) {
            $this->delete($forum);
        }

        $topics = $this->topicRepository
            ->findBy(
                [
                    'id' => $forumEntity->id,
                ]
            );
        
        foreach ($topics as $topic) {
            $this->topicFacade->delete($topic);
        }

        $this->em->remove($forumEntity);
        $this->em->flush();
    }
}
