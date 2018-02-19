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
class TopicsManager extends Crud\CrudManager {

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
     * @var TopicWatchManager $topicWatchManager 
     */
    private $topicWatchManager;

    /**
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi) {
        parent::__construct($dibi);
    }

    /**
     * @param ForumsManager $forumManager
     */
    public function injectForumManager(ForumsManager $forumManager) {
        $this->forumManager = $forumManager;
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager) {
        $this->userManager = $userManager;
    }

    /**
     * @param ThanksManager $thanksManager
     */
    public function injectThankManager(ThanksManager $thanksManager) {
        $this->thanksManager = $thanksManager;
    }

    /**
     * @param PostsManager $postManager
     */
    public function injectPostManager(PostsManager $postManager) {
        $this->postManager = $postManager;
    }
    
    public function add(ArrayHash $item_data) {
        $values = clone $item_data;

        $values->topic_name = $item_data->post_title;
        $values->topic_user_id = $item_data->post_user_id;
        $values->topic_forum_id = $item_data->post_forum_id;

        unset($values->post_title);
        unset($values->post_text);
        unset($values->post_add_time);
        unset($values->post_user_id);
        unset($values->post_forum_id);

        $topic_id = parent::add($values);

        $item_data->post_topic_id = $topic_id;
        $this->postManager->add($item_data);
        $this->userManager->update($values->topic_user_id, ArrayHash::from(['user_topic_count%sql' => 'user_topic_count + 1']));
        
        $this->topicWatchManager = new TopicWatchManager($this->dibi, $this, $this->userManager, self::TOPIC_WATCH_TABLE);        
        $this->topicWatchManager->add([$values->topic_user_id], $topic_id);

        return $topic_id;
    }

    /**
     * @param int $topic_id
     *
     * @return Result|int|void
     */
    public function delete($topic_id) {
        $topic = $this->getById($topic_id);

        $thanks = $this->thanksManager->getThanksByTopicId($topic_id);

        foreach ($thanks as $thank) {
            $this->userManager->update($thank->thank_user_id, ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1']));
        }

        $this->thanksManager->deleteByTopicId($topic_id);

        $counts = $this->postManager->getCountOfUsersByTopicId($topic_id);

        foreach ($counts as $count) {
            $this->userManager->update($count->post_user_id, \Nette\Utils\ArrayHash::from(['user_post_count%sql' => 'user_post_count - ' . $count->post_count]));
        }

        $this->userManager->update($topic->topic_user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1']));
        $this->postManager->deleteByTopicId($topic_id);

        return parent::delete($topic_id);
    }

    /**
     * @param string $topic_name
     *
     * @return array
     */
    public function findTopicsByTopicName($topic_name) {
        return $this->dibi->select('*')
                        ->from($this->getTable())
                        ->where('MATCH([topic_name]) AGAINST (%s IN BOOLEAN MODE)', $topic_name)
                        ->fetchAll();
    }

    public function getLastTopicByForumId($forum_id) {
        return $this->dibi->query('SELECT * FROM [' . self::TOPICS_TABLE . '] WHERE [topic_id] = ( SELECT MAX(topic_id) FROM [' . self::TOPICS_TABLE . '] WHERE [topic_forum_id] = %i )', $forum_id)->fetch();
    }
    

    /**
     * @return Row|false
     */
    public function getLastTopic()
    {
        return $this->dibi->query('SELECT * FROM ['.self::TOPICS_TABLE.'] WHERE [topic_id] = (SELECT MAX(topic_id) FROM ['.self::TOPICS_TABLE.'])')->fetch();
    }    
    
    public function getNewerTopics($forum_id, $topic_time){
        $cache  = new \Nette\Caching\Cache($this->storage,$this->getTable());
        $key    = $forum_id.'-'.$topic_time;
        $cached = $cache->load($key);          
        
        if (!isset($cached)){
            $cache->save($key, $cached = $this->dibi->select('*')->from($this->getTable())->where('[topic_forum_id] = %i', $forum_id)->where('[topic_add_time] > %i', $topic_time)->fetchAll(), [
                \Nette\Caching\Cache::EXPIRE => '2 hours',
            ]);
        }
        
        return $cached;
    }
    
}
