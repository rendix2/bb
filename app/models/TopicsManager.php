<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of TopicsManager
 *
 * @author rendi
 */
class TopicsManager extends Crud\CrudManager {

    /**
     *
     * @var \App\Models\PostsManager $postManager 
     */
    private $postManager;
    
    /**
     *
     * @var \App\Models\UsersManager $userManager 
     */
    private $userManager;
    
    /**
     *
     * @var \App\Models\ThanksManager $thanksManager
     */
    private $thanksManager;
    
    private $forumManager;

    /**
     * 
     * @param \Dibi\Connection $dibi
     * @param \App\Models\PostsManager $postManager
     * @param \App\Models\UsersManager $userManager
     * @param \App\Models\ThanksManager $thanksManager
     */
    public function __construct(\Dibi\Connection $dibi, \App\Models\PostsManager $postManager, \App\Models\UsersManager $userManager, \App\Models\ThanksManager $thanksManager, \App\Models\ForumsManager $forumManager) {
        parent::__construct($dibi);

        $this->thanksManager = $thanksManager;
        $this->userManager = $userManager;
        $this->postManager = $postManager;
        $this->forumManager = $forumManager;
    }
    
    public function injectForumManager(){
        
    }

    public function delete($topic_id) {
        $this->dibi->begin();

        $topic  = $this->getById($topic_id);
        $thanks = $this->thanksManager->getThanksByTopicId($topic_id);

        foreach ($thanks as $thank) {
            $this->userManager->update($thank->thank_user_id, \Nette\Utils\ArrayHash::from(['user_thank_count%sql' => 'user_thank_count - 1']));
        }

        $this->forumManager->update($topic->topic_forum_id, \Nette\Utils\ArrayHash::from(['forum_topic_count%sql' => 'forum_topic_count - 1']));
        $this->userManager->update($topic->topic_user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count - 1', 'user_post_count%sql' => 'user_post_count - 1']));
        $this->postManager->deleteByTopicId($topic_id);

        parent::delete($topic_id);

        $this->dibi->commit();
    }
    
    public function findTopicsByTopicName($topic_name){
        return $this->dibi->select('*')->from($this->getTable())->where('MATCH([topic_name]) AGAINST (%s IN BOOLEAN MODE)', $topic_name)->fetchAll();     
    }
    
    public function getLastTopic(){
        return $this->dibi->select('*')->from($this->getTable())->orderBy('topic_id', \dibi::DESC)->fetch();
    }
    
}
