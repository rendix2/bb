<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Models\Entity\ForumEntity;
use App\Models\Entity\TopicEntity;
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
     * @var ForumManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var TopicFacade $topicFacade
     */
    private $topicFacade;
    
    /**
     *
     * @var TopicManager $topicsManager
     */
    private $topicsManager;

    /**
     *
     * ForumFacade constructor.
     *
     * @param ForumManager $forumsManager
     * @param TopicFacade   $topicFacade
     * @param TopicManager $topicsManager
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        ForumManager $forumsManager,
        TopicFacade   $topicFacade,
        TopicManager $topicsManager
    ) {
        $this->forumsManager = $forumsManager;
        $this->topicFacade   = $topicFacade;
        $this->topicsManager = $topicsManager;
    }

    /**
     *  ForumFacade destructor.
     */
    public function __destruct()
    {
        $this->topicFacade   = null;
        $this->topicsManager = null;
        $this->forumsManager = null;
    }


    /**
     * @param int       $itemId
     * @param ArrayHash $itemData
     * @return bool
     */
    public function update(int $itemId, ArrayHash $itemData): bool
    {
        $forum = $this->em
            ->getRepository(\App\Model\Entity\ForumEntity::class)
            ->find($itemId);

        if ($forum === null) {
            return false;
        }

        if (isset($itemData->forum_parent_id)) {
            $parent = $itemData->forum_parent_id
                ? $this->em->getRepository(\App\Model\Entity\ForumEntity::class)->find($itemData->forum_parent_id)
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

    /**
     * @param ForumEntity $forum
     *
     * @return bool
     */
    public function delete(ForumEntity $forum)
    {
        $forums = $this->forumsManager->getAllByParent($forum->getForum_id());

        foreach ($forums as $forumDibi) {
            $forum = ForumEntity::setFromRow($forumDibi);
            $this->delete($forum);
        }

        $topics = $this->topicsManager->getAllByForum($forum->getForum_id());
        
        foreach ($topics as $topicDibi) {
            $topic = TopicEntity::setFromRow($topicDibi);
            
            $this->topicFacade->delete($topic);
        }
 
        return $this->forumsManager->delete($forum->getForum_id());
    }
}
