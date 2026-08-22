<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ReportRepository;


class ReportFacade
{

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly PostRepository  $postRepository,

        private readonly ReportRepository $reportRepository,

    ) {
    }

    public function deleteByTopic(\App\Model\Entity\TopicEntity $topicEntity): void
    {
        $posts = $this->postRepository->findByTopic($topicEntity);

        $reportedPosts = $this->reportRepository->findByPosts($posts);

        foreach ($reportedPosts as $reportedPost) {
            $this->em->remove($reportedPost);
        }

        $this->em->flush();

        $reportedTopics = $this->reportRepository->findByTopic($topicEntity);

        foreach ($reportedTopics as $reportedTopic) {
            $this->em->remove($reportedTopic);
        }

        $this->em->flush();
    }
}
