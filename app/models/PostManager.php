<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Nette\Utils\ArrayHash;

/**
 * Description of PostManager
 *
 * @author rendix2
 * @package App\Models
 */
class PostManager extends CrudManager
{

    public function copy($post_id, $target_topic_id = null)
    {
        $post = $this->getById($post_id);
        
        unset($post->post_id);
        
        if ($target_topic_id) {
            $post->post_topic_id = $target_topic_id;
        }
                
        return $this->add(ArrayHash::from($post->toArray()));
    }

    public function checkDoublePost($post_text, $user_id, $time)
    {
        return $this->dibi
            ->select('1')
            ->from($this->getTable())
            ->where('[post_text] = %s', $post_text)
            ->where('[post_user_id] = %i', $user_id)
            ->where('[post_add_time] >= %i', $time)
            ->fetchSingle();
    }
}
