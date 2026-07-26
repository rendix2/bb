<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\Entity\TopicEntity;
use App\Utils;

/**
 * Description of ReportFacade
 *
 * @author rendix2
 * @package App\Models
 */
class ReportFacade
{

    /**
     * @var PostManager $postsManager
     */
    private PostManager $postsManager;
    
    /**
     *
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportManager;
    
    /**
     *
     * @var TopicManager $topicsManager
     */
    private TopicManager $topicsManager;
    
    /**
     *
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;

    /**
     *
     * ReportFacade constructor
     *
     * @param ForumManager $forumsManager
     * @param TopicManager $topicsManager
     * @param PostManager $postsManager
     * @param ReportManager $reportsManager
     */
    public function __construct(
        ForumManager  $forumsManager,
        TopicManager  $topicsManager,
        PostManager   $postsManager,
        ReportManager $reportsManager,
        private readonly EntityManagerDecorator $em,
    ) {
        $this->forumsManager  = $forumsManager;
        $this->topicsManager  = $topicsManager;
        $this->postsManager   = $postsManager;
        $this->reportManager  = $reportsManager;
    }

    /**
     *
     * @param int $category_id
     *
     * @return void
     */
    public function deleteByCategory($category_id)
    {
        $forums = $this->em
            ->getRepository(ForumEntity::class)
            ->findBy(
                [
                    'category' => $category_id,
                ]
            );
        
        foreach ($forums as $forum) {
            $this->deleteByForum($forum->forum_id);
        }
    }
    
    /**
     *
     * @param int $forum_id
     *
     * @return bool
     */
    public function deleteByForum($forum_id)
    {
        $forumEntity = $this->em
            ->getRepository(ForumEntity::class)
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topics = $this->em
            ->getRepository(\App\Model\Entity\TopicEntity::class)
            ->findBy(
                [
                    'forum' => $forumEntity,
                ]
            );
        
        foreach ($topics as $topicEntity) {
            $this->deleteByTopic($topicEntity);
        }
        
        return $this->reportManager->deleteByForum($forum_id);
    }

    /**
     *
     * @param \App\Model\Entity\TopicEntity $topicEntity
     * @return  bool
     */
    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $posts     = $this->postsManager->getFluentByTopic($topicEntity->id);
        $posts_ids = Utils::arrayObjectColumn($posts, 'post_id');
        
        $this->reportManager->deleteByPosts($posts_ids);
        return $this->reportManager->deleteByTopic($topicEntity->id);
    }

    /**
     *
     * @param int $post_id
     *
     * @return bool
     */
    public function deleteByPost($post_id)
    {
        return $this->reportManager->deleteByPost($post_id);
    }
}
