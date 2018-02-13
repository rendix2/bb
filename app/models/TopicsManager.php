<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Dibi\Connection;
use Dibi\Result;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicsManager
 *
 * @author rendi
 */
class TopicsManager extends Crud\CrudManager
{

    /**
     *
     * @var PostsManager $postManager
     */
    private $postManager;

    /**
     *
     * @var \App\Models\UsersManager $userManager
     */
    private $userManager;

    /**
     *
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;

    /**
     *
     * @var ForumsManager $forumManager
     */
    private $forumManager;

    /**
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi)
    {
        parent::__construct($dibi);
    }

    /**
     * @param ForumsManager $forumManager
     */
    public function injectForumManager(ForumsManager $forumManager)
    {
        $this->forumManager = $forumManager;
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param ThanksManager $thanksManager
     */
    public function injectThankManager(ThanksManager $thanksManager)
    {
        $this->thanksManager = $thanksManager;
    }

    /**
     * @param PostsManager $postManager
     */
    public function injectPostManager(PostsManager $postManager)
    {
        $this->postManager = $postManager;
    }

    /**
     * @param int $topic_id
     *
     * @return Result|int|void
     */
    public function delete($topic_id)
    {
        $this->dibi->begin();

        $topic           = $this->getById($topic_id);
        $forum           = $this->forumManager->getById($topic->topic_forum_id);
        $lastPostByForum = $this->postManager->getLastPostByForum($topic->topic_forum_id, 0, $topic_id);
        $forum_id        = $topic->topic_forum_id;

        if ((int)$topic_id === $forum->forum_last_topic_id) {
            $this->update($topic_id, ArrayHash::from([
                'topic_last_post_id'      => $lastPostByForum->post_id,
                'topic_last_post_user_id' => $lastPostByForum->post_user_id
            ]));
            $this->forumManager->update($forum_id, ArrayHash::from([
                'forum_last_topic_id'     => $lastPostByForum->post_topic_id,
                'forum_last_post_id'      => $lastPostByForum->post_id,
                'forum_last_post_user_id' => $lastPostByForum->post_user_id
            ]));
        } else {
            $this->update($topic_id, ArrayHash::from([
                'topic_last_post_id'      => $lastPostByForum->post_id,
                'topic_last_post_user_id' => $lastPostByForum->post_user_id
            ]));
            $this->forumManager->update($forum_id, ArrayHash::from([
                'forum_last_post_id'      => $lastPostByForum->post_id,
                'forum_last_post_user_id' => $lastPostByForum->post_user_id
            ]));
        }

        $thanks = $this->thanksManager->getThanksByTopicId($topic_id);

        foreach ($thanks as $thank) {
            $this->userManager->update($thank->thank_user_id, ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1']));
        }

        $this->thanksManager->deleteByTopicId($topic_id);
        $this->forumManager->update($topic->topic_forum_id, \Nette\Utils\ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1']));
        
        $counts = $this->postManager->getCountOfUsersByTopicId($topic_id);
        
        foreach ( $counts as $count){
            $this->userManager->update($count->post_user_id, \Nette\Utils\ArrayHash::from(['user_post_count%sql' => 'user_post_count - '.$count->post_count]));
        }
                     
        $this->userManager->update($topic->topic_user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));
        $this->postManager->deleteByTopicId($topic_id);

        parent::delete($topic_id);

        $this->dibi->commit();
    }

    /**
     * @param string $topic_name
     *
     * @return array
     */
    public function findTopicsByTopicName($topic_name)
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->where('MATCH([topic_name]) AGAINST (%s IN BOOLEAN MODE)', $topic_name)
                          ->fetchAll();
    }

    /**
     * @return Row|false
     */
    public function getLastTopic()
    {
        return $this->dibi->select('*')->from($this->getTable())->orderBy('topic_id', \dibi::DESC)->fetch();
    }

}
