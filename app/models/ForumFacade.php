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
    /**
     *
     * @var TopicFacade $topicFacade
     */
    private TopicFacade $topicFacade;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,

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
        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $itemId,
                ]
            );

        if ($forumEntity === null) {
            return false;
        }

        if (isset($itemData->forum_parent_id)) {
            $parent = $itemData->forum_parent_id
                ? $this->forumRepository->find($itemData->forum_parent_id)
                : null;

            $forumEntity->parent($parent);

            unset($itemData->forum_parent_id);
        }

        if (isset($itemData->forum_name)) {
            $forumEntity->setName($itemData->forum_name);
        }

        $this->em->flush();

        return true;
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
