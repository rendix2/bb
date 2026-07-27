<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use Nette\Utils\ArrayHash;

/**
 * Description of ForumFacade
 *
 * @author rendix2
 * @package App\Models
 */
class ForumFacade
{
    /**
     *
     * @var TopicFacade $topicFacade
     */
    private TopicFacade $topicFacade;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        TopicFacade   $topicFacade,
    ) {
        $this->topicFacade   = $topicFacade;
    }

    /**
     * @param int       $itemId
     * @param ArrayHash $itemData
     * @return bool
     */
    public function update(int $itemId, ArrayHash $itemData): bool
    {
        $forum = $this->em
            ->getRepository(ForumEntity::class)
            ->find($itemId);

        if ($forum === null) {
            return false;
        }

        if (isset($itemData->forum_parent_id)) {
            $parent = $itemData->forum_parent_id
                ? $this->em->getRepository(ForumEntity::class)->find($itemData->forum_parent_id)
                : null;

            $forum->parent($parent);

            unset($itemData->forum_parent_id);
        }

        if (isset($itemData->forum_name)) {
            $forum->setName($itemData->forum_name);
        }

        $this->em->flush();

        return true;
    }

    public function delete(ForumEntity $forumEntity): void
    {
        $forums = $this->em
            ->getRepository(ForumEntity::class)
            ->findBy(
                [
                    'parent' => $forumEntity,
                ]
            );

        foreach ($forums as $forum) {
            $this->delete($forum);
        }

        $topics = $this->em
            ->getRepository(TopicEntity::class)
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
