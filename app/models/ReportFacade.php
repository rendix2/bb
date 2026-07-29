<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ReportRepository;
use App\Model\Repository\TopicRepository;
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
     *
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportManager;

    public function __construct(
        ReportManager $reportsManager,
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
        private readonly PostRepository  $postRepository,

        private readonly ReportRepository $reportRepository,
    ) {
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
        $forums = $this->forumRepository
            ->findBy(
                [
                    'category' => $category_id,
                ]
            );
        
        foreach ($forums as $forum) {
            $this->deleteByForumId($forum->forum_id);
        }
    }
    
    public function deleteByForumId(int $forum_id)
    {
        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topics = $this->topicRepository
            ->findBy(
                [
                    'forum' => $forumEntity,
                ]
            );
        
        foreach ($topics as $topicEntity) {
            $this->deleteByTopic($topicEntity);
        }

        $reports = $this->reportRepository->findByForum($forumEntity);

        foreach ($reports as $report) {
            $this->em->remove($report);
        }

        $this->em->flush();
    }

    /**
     *
     * @param \App\Model\Entity\TopicEntity $topicEntity
     * @return  bool
     */
    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity)
    {
        $posts = $this->postRepository->findByTopic($topicEntity);
        $posts_ids = Utils::arrayObjectColumn($posts, 'id');
        
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
