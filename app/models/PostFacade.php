<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;
use App\Settings\TopicsSetting;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of PostFacade
 *
 * @author rendix2
 * @package App\Models
 */
class PostFacade
{
    /**
     *
     * @var PostManager $postsManager
     */
    private $postsManager;
    
    /**
     *
     * @var TopicManager $topicsManager
     */
    private TopicManager $topicsManager;

    
    /**
     *
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;
    
    /**
     *
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportsManager;
    
    /**
     *
     * @var ForumManager $forumsManager
     */
    private ForumManager $forumsManager;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     */
    private PostsHistoryManager $postsHistoryManager;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     */
    private ThanksFacade $thanksFacade;
    
    /**
     *
     * @var TopicWatchFacade $topicWatchFacade
     */
    private TopicWatchFacade $topicWatchFacade;
    
    /**
     *
     * @var PollsFacade $pollsFacade
     */
    private PollsFacade $pollsFacade;
    
    /**
     *
     * @var TopicsSetting $topicSettings
     */
    private TopicsSetting $topicSettings;
    
    /**
     *
     * @var FilesManager $filesManager
     */
    private FilesManager $filesManager;
    
    /**
     * @var Posts2FilesManager $posts2FilesManger
     */
    private Posts2FilesManager $posts2FilesManger;

    /**
     *
     * PostFacade constructor.
     *
     * @param PostManager $postsManager
     * @param TopicManager $topicsManager
     * @param UsersManager $usersManager
     * @param ReportManager $reportsManager
     * @param ForumManager $forumsManager
     * @param PostsHistoryManager $postsHistoryManager
     * @param ThanksFacade $thanksFacade
     * @param TopicWatchFacade $topicWatchFacade
     * @param PollsFacade $pollsFacade
     * @param TopicsSetting $topicSettings
     * @param FilesManager $filesManager
     * @param Posts2FilesManager $posts2FilesManger
     * @param EntityManagerDecorator $em
     */
    public function __construct(
        PostManager        $postsManager,
        TopicManager       $topicsManager,
        UsersManager        $usersManager,
        ReportManager      $reportsManager,
        ForumManager       $forumsManager,
        PostsHistoryManager $postsHistoryManager,
        ThanksFacade        $thanksFacade,
        TopicWatchFacade    $topicWatchFacade,
        PollsFacade         $pollsFacade,
        TopicsSetting       $topicSettings,
        FilesManager        $filesManager,
        Posts2FilesManager  $posts2FilesManger,
        private readonly EntityManagerDecorator $em
    ) {
        $this->postsManager        = $postsManager;
        $this->topicsManager       = $topicsManager;
        $this->usersManager        = $usersManager;
        $this->reportsManager      = $reportsManager;
        $this->forumsManager       = $forumsManager;
        $this->postsHistoryManager = $postsHistoryManager;
        $this->thanksFacade        = $thanksFacade;
        $this->topicWatchFacade    = $topicWatchFacade;
        $this->pollsFacade         = $pollsFacade;
        $this->topicSettings       = $topicSettings;
        $this->filesManager        = $filesManager;
        $this->posts2FilesManger   = $posts2FilesManger;
    }

    /**
     * @param PostEntity $post
     *
     * @return Result|int
     */
    public function add(PostEntity $post)
    {
        $post_id  = $this->postsManager->add($post->getArrayHash());
        $user_id  = $post->getPost_user_id();
        $forum_id = $post->getPost_forum_id();
        $topic_id = $post->getPost_topic_id();
        
        $topicDibi = $this->topicsManager->getById($topic_id);
        $topic     = TopicEntity::setFromRow($topicDibi);
        
        $files     = $post->getPost_files();
        $files_ids = [];
        
        if ($files) {
            foreach ($files as $file) {
                $file_id = $this->filesManager->add($file->getArrayHash());
                
                $file->setFile_id($file_id);
                $files_ids[] = $file->getFile_id();
            }
            
            $this->posts2FilesManger->addByLeft($post_id, $files_ids);
        }

        $this->topicsManager->update(
            $topic_id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count + 1',
                'topic_last_user_id'   => $user_id,
                'topic_last_post_id'   => $post_id,
                'topic_page_count'     => ceil(($topic->getTopic_post_count() + 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
                ])
        );

        $topicEntity = $this->em
            ->getRepository(TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $topicWatchEntity = $this->em
            ->getRepository(TopicWatchEntity::class)
            ->findOneBy(
                [
                    'topic' => $topicEntity,
                    'user' => $userEntity,
                ]
            );

        $watch = [];

        if ($topicWatchEntity === null) {
            $topicWatchEntity = new TopicWatchEntity();
            $topicWatchEntity->topic = $topicEntity;
            $topicWatchEntity->user = $userEntity;

            $this->em->persist($topicWatchEntity);
            $this->em->flush();

            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $this->postsHistoryManager->add(ArrayHash::from([
                'post_id'           => $post_id,
                'post_user_id'      => $user_id,
                'post_title'        => $post->getPost_title(),
                'post_text'         => $post->getPost_text(),
                'post_history_time' => time()
            ]));

        $this->usersManager->update($user_id, ArrayHash::from([
                'user_post_count%sql' => 'user_post_count + 1',
                'user_last_post_time' => time()
            ] + $watch));
        
        $this->forumsManager->update($forum_id, ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1']));

        return $post_id;
    }

    /**
     * @param PostEntity $post
     *
     * @return bool
     */
    public function update(PostEntity $post)
    {
        //$myPost = clone $post;
        //unset($myPost->post_id);

        $add = $this->postsHistoryManager->add(ArrayHash::from([
            'post_id' => $post->getPost_id(),
            'post_user_id' => $post->getPost_user_id(),
            'post_title' => $post->getPost_title(),
            'post_text' => $post->getPost_text(),
            'post_history_time' => time()
        ]));

        $post->setPost_user_id(null);
        
        $update = $this->postsManager->update($post->getPost_id(), $post->getArrayHash());

        return $update && $add;
    }

    /**
     * @param \App\Model\Entity\TopicEntity $topicEntity
     * @param \App\Model\Entity\PostEntity $postEntity
     * @return Result|int
     */
    public function delete(\App\Model\Entity\TopicEntity $topicEntity , \App\Model\Entity\PostEntity $postEntity)
    {
        $this->usersManager->update(
            $postEntity->user->id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1'])
        );
        $this->topicsManager->update(
            $postEntity->topic->id,
            ArrayHash::from([
                'topic_post_count%sql' => 'topic_post_count - 1',
                'topic_page_count'     => ceil(($topicEntity->getTopic_post_count() - 1) / $this->topicSettings->get()['pagination']['itemsPerPage'])
            ])
        );

        $this->thanksFacade->deleteByPost($postEntity);
        $this->postsHistoryManager->deleteByPost($postEntity->id);
        $this->topicWatchFacade->deleteByPost($postEntity);
        $this->reportsManager->deleteByPost($postEntity->id);
        $this->forumsManager->update(
            $postEntity->forum->id,
            ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
        );
        
        // recount last post info
        $res = $this->postsManager->delete($postEntity->id);
                
        // last post
        if ($topic->getTopic_last_post_id() === (int)$postEntity->id && $topic->getTopic_first_post_id() !== (int)$postEntity->id) {
            $last_post = $this->postsManager->getLastByTopic($postEntity->topic->id);

            if ($last_post) {
                $this->topicsManager->update($postEntity->topic->id, ArrayHash::from([
                    'topic_last_post_id' => $last_post->post_id,
                    'topic_last_user_id' => $last_post->post_user_id
                ]));
            }
        } elseif ($topic->getTopic_first_post_id() === (int)$postEntity->id && $topic->getTopic_last_post_id() !== (int)$postEntity->id) {
            $first_post = $this->postsManager->getFirstByTopic($post->getPost_topic_id());
            
            if ($first_post) {
                $this->topicsManager->update($post->getPost_topic_id(), ArrayHash::from([
                    'topic_first_post_id' => $first_post->post_id,
                    'topic_first_user_id' => $first_post->post_user_id
                ]));
            }
        } elseif ($topic->getTopic_last_post_id() === $topic->getTopic_first_post_id() && $topic->getTopic_first_post_id() === (int)$postEntity->id) {
            $this->forumsManager->update(
                $postEntity->forum->id,
                ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1'])
            );
            $this->thanksFacade->deleteByTopic($topic);
            $this->reportsManager->deleteByTopic($topic->getTopic_id());
            $this->topicWatchFacade->deleteByTopic($topic);
            $this->usersManager->update(
                $topic->getTopic_user_id(),
                ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1'])
            );
            
            if ($topic->getPoll()) {
                $this->pollsFacade->delete($topic->getPoll());
            }
            
            $this->topicsManager->delete($topic->getTopic_id());

            return 2;
        }
        
        $lastPostOfUser = $this->postsManager->getLastByUser($postEntity->user->id);

        if ($lastPostOfUser) {
            $this->usersManager->update(
                $postEntity->user->id,
                ArrayHash::from(['user_last_post_time' => $lastPostOfUser->post_add_time])
            );
        } else {
            $this->usersManager->update(
                $postEntity->user->id,
                ArrayHash::from(['user_last_post_time' => 0])
            );
        }
        
        return $res;
    }
    
    /**
     *
     * @param int $post_id
     * @param int $target_topic_id
     *
     * @return boolean
     */
    public function move($post_id, $target_topic_id)
    {
        $post = $this->postsManager->getById($post_id);
       
        if (!$post) {
            return false;
        }
        
        $target_topic = $this->topicsManager->getById($target_topic_id);
        
        if (!$target_topic) {
            return false;
        }
        
        $source_topic_id = $post->post_topic_id;
        $source_forum_id = $post->post_forum_id;
                
        $target_forum_id = $target_topic->topic_forum_id;
       
        if ($source_topic_id !== $target_topic_id) {
            $this->topicsManager->update(
                $source_topic_id,
                ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1'])
            );
            $this->topicsManager->update(
                $target_topic_id,
                ArrayHash::from(['topic_post_count%sql' => 'topic_post_count + 1'])
            );
        }
        
        if ($source_forum_id !== $target_forum_id) {
            $this->forumsManager->update(
                $source_forum_id,
                ArrayHash::from(['forum_post_count%sql' => 'forum_post_count - 1'])
            );
            $this->forumsManager->update(
                $target_forum_id,
                ArrayHash::from(['forum_post_count%sql' => 'forum_post_count + 1'])
            );
        }
        
        $this->reportsManager->updateByPost($post_id, ArrayHash::from(['report_topic_id' => $target_topic_id]));
        
        return $this->postsManager->update($post_id, ArrayHash::from(['post_topic_id' => $target_topic_id]));
    }
}
