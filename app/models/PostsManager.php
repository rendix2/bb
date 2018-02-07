<?php

namespace App\Models;

/**
 * Description of PostManager
 *
 * @author rendi
 */
class PostsManager extends Crud\CrudManager {

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

}
