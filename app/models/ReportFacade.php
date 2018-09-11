<?php

namespace App\Models;

/**
 * Description of ReportFacade
 *
 * @author rendix2
 */
class ReportFacade
{

    /**
     * @var PostsManager $postsManager
     */
    private $postsManager;
    
    /**
     *
     * @var ReportsManager $reportsManager
     */
    private $reportManager;
    
    /**
     *
     * @var TopicsManager $topicsManager
     */
    private $topicsManager;
    
    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;


    /**
     *
     * @param ForumsManager $forumsManager
     * @param TopicsManager $topicsManager
     * @param PostsManager $postsManager
     * @param ReportsManager $reportsManager
     */
    public function __construct(
        ForumsManager $forumsManager,
        TopicsManager $topicsManager,
        PostsManager $postsManager,
        ReportsManager $reportsManager
    ) {
        $this->forumsManager = $forumsManager;
        $this->topicsManager     = $topicsManager;
        $this->postsManager      = $postsManager;
        $this->reportManager     = $reportsManager;
    }
    
    /**
     *
     * @param int $category_id
     *
     * @return bool
     */
    public function deleteByCategory($category_id)
    {
        $forums = $this->forumsManager->getAllByCategory($category_id);
        
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
        $topics = $this->topicsManager->getAllByForum($forum_id);
        
        foreach ($topics as $topic) {
            $this->deleteByTopic($topic->topic_id);
        }
        
        return $this->reportManager->deleteByForum($forum_id);
    }
    
    /**
     *
     * @param int $topic_id
     *
     * @return  bool
     */
    public function deleteByTopic($topic_id)
    {
        $posts     = $this->postsManager->getByTopic($topic_id);
        $posts_ids = [];
        
        foreach ($posts as $post) {
            $posts_ids[] = $post->post_id;
        }
        
        $this->reportManager->deleteByPosts($posts_ids);
        return $this->reportManager->deleteByTopic($topic_id);
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
