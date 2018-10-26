<?php

namespace App\Models\Traits;

use App\Models\Entity\Forum;
use App\Models\ForumsManager;
use Nette\Http\IResponse;

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

        $forumDibi = $this->forumsManager->getById($forum_id);

        if (!$forumDibi) {
            $this->error('Forum was not found.');
        }
        
        $forum = Forum::setFromRow($forumDibi);

        if ($category_id) {
            if ($forum->getForum_category_id() !== (int)$category_id) {
                $this->error('Category param does not match.');
            }
        }

        if (!$forum->getForum_active()) {
            $this->error('Forum is not active.');
        }

        return $forum;
    }   
}
