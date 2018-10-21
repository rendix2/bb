<?php

namespace App\Models\Traits;

use App\Models\ForumsManager;

/**
 * Description of ForumsTrait
 *
 * @author rendix2
 */
trait ForumsTrait
{
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;  
    
    /**
     * 
     * @param int $forum_id
     * @param int $category_id
     * 
     * @return \App\Models\Entity\Forum
     * ¨
     */
    public function checkForumParam($forum_id, $category_id = null)
    {
        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($category_id) {
            if ($forum->forum_category_id !== (int)$category_id) {
                $this->error('Category param does not match.');
            }
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        return $forum;
    }   
}
