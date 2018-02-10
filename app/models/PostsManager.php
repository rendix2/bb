<?php

namespace App\Models;

/**
 * Description of PostManager
 *
 * @author rendi
 */
class PostsManager extends Crud\CrudManager {
    
    private $topicsManager;
    private $forumManager;
    private $userManager;     
    
    public function set(){
        $this->topicsManager = new TopicsManager($this->dibi);
        $this->topicsManager->factory($this->getStorage());
        $this->forumManager = new ForumsManager($this->dibi);
        $this->forumManager->factory($this->getStorage());
        $this->userManager = new UsersManager($this->dibi);
        $this->userManager->factory($this->getStorage());
    }

    public function getPostsByTopicId($topic_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[post_topic_id] = %i', $topic_id);
    }

    public function getCountOfPostsInTopic($topic_id) {
        return $this->dibi->select('COUNT(post_id)')->from($this->getTable())->where('[post_topic_id] = %i', $topic_id)->fetchSingle();
    }

    public function getCountOfPostsInForum($forum_id) {
        return $this->dibi->select('COUNT(post_id)')->from($this->getTable())->where('[post_forum_id] = %i', $forum_id)->fetchSingle();
    }

    public function getCountOfPostsInCategory($category_id) {
        return $this->dibi->select('COUNT(post_id)')->from($this->getTable())->where('[post_category_id] = %i', $category_id)->fetchSingle();
    }

    public function deleteByTopicId($topic_id) {
        return $this->dibi->delete($this->getTable())->where('[post_topic_id] = %i', $topic_id)->execute();
    }
    
    public function findPosts($post_text){
        return $this->dibi->select('*')->from($this->getTable())->as('p')->leftJoin(self::TOPICS_TABLE)->as('t')->on('[p.post_topic_id] = [t.topic_id]')->where('MATCH([p.post_title],[p.post_text]) AGAINST(%s IN BOOLEAN MODE)', $post_text)->fetchAll();     
    }
    
    public function getLastPostByTopic($topic_id, $post_id){
        return $this->dibi->select('*')->from($this->getTable())->where('[post_topic_id] = %i', $topic_id)->where('[post_id] < %i', $post_id)->orderBy('post_id', \dibi::DESC)->fetch();
    }
    
    public function getLastPostByForum($forum_id, $post_id, $topic_id){
        
        $q = $this->dibi->select('*')->from($this->getTable())->where('[post_forum_id] = %i', $forum_id);
        
        if ( $post_id > 0 ){
          $q->where('[post_id] < %i', $post_id);  
        }
        else{
            $q->where('[post_topic_id] < %i', $topic_id);
        }             
        
        return $q->orderBy('post_id', \dibi::DESC)->fetch();        
    }
    
    public function delete($item_id) {
        $post            = $this->getById($item_id);
        $topic           = $this->topicsManager->getById($post->post_topic_id);
        $lastPost        = $this->getLastPostByTopic($post->post_topic_id, $item_id);
        $lastPostByForum = $this->getLastPostByForum($post->post_forum_id, $item_id, $post->post_topic_id);

        if ((int) $item_id === $topic->topic_last_post_id) {
            if ($lastPost) {
                $this->topicsManager->update($post->post_topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => $lastPost->post_id, 'topic_last_post_user_id' => $lastPost->post_user_id]));
                $this->forumManager->update($post->post_forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $lastPostByForum->post_topic_id, 'forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
            } else {
                $this->topicsManager->update($post->post_topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => 0, 'topic_last_post_user_id' => 0]));
                $this->forumManager->update($post->post_forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $lastPostByForum->post_topic_id, 'forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
            }
        }

        $this->topicsManager->update($post->post_topic_id, \Nette\Utils\ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1']));       
        $this->userManager->update($post->post_user_id, \Nette\Utils\ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1']));
       
        parent::delete($item_id);
    }
    
    public function add(\Nette\Utils\ArrayHash $item_data) {            
        $post_id = parent::add($item_data);
        $user_id = $item_data->post_user_id;
        $topic_id = $item_data->post_topic_id;
        $forum_id = $item_data->post_forum_id;
               
        $this->userManager->update($user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count + 1']));
        $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_post_count%sql' => 'topic_post_count+1', 'topic_last_post_user_id' => $user_id, 'topic_last_post_id' => $post_id]));
        $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $topic_id, 'forum_last_post_id' => $post_id, 'forum_last_post_user_id' => $user_id]));
        
        return $post_id;
    }

}
