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

        private readonly PostRepository  $postRepository,

    ) {
        $this->reportManager  = $reportsManager;
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
}
