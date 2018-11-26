<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of ForumEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class ForumEntity extends Entity
{
    /**
     *
     * @var int $forum_id
     */
    private $forum_id;
    
    /**
     *
     * @var int $forum_category_id
     */
    private $forum_category_id;
    
    /**
     *
     * @var string $forum_name
     */
    private $forum_name;
    
    /**
     *
     * @var string $forum_description
     */
    private $forum_description;
    
    /**
     *
     * @var bool $forum_active
     */
    private $forum_active;
    
    /**
     *
     * @var int $forum_parent_id
     */
    private $forum_parent_id;
    
    /**
     *
     * @var int $forum_order
     */
    private $forum_order;
    
    /**
     *
     * @var bool $forum_thank
     */
    private $forum_thank;
    
    /**
     *
     * @var int $forum_post_count
     */
    private $forum_post_count;
    
    /**
     *
     * @var int $forum_topic_count
     */
    private $forum_topic_count;

    /**
     *
     * @var bool $forum_post_add
     */
    private $forum_post_add;
    
    /**
     *
     * @var bool $forum_post_delete
     */
    private $forum_post_delete;
    
    /**
     *
     * @var bool $forum_post_update
     */
    private $forum_post_update;

    /**
     *
     * @var bool $forum_topic_add
     */
    private $forum_topic_add;
       
    /**
     *
     * @var bool $forum_topic_update
     */
    private $forum_topic_update;
    
    /**
     *
     * @var bool $forum_topic_delete
     */
    private $forum_topic_delete;
    
    /**
     *
     * @var bool $forum_fast_reply
     */
    private $forum_fast_reply;

    /**
     *
     * @var string $forum_rules
     */
    private $forum_rules;

    /**
     *
     * @var int $forum_left
     */
    private $forum_left;
    
    /**
     *
     * @var int $forum_right
     */
    private $forum_right;

    public function getForum_id()
    {
        return $this->forum_id;
    }

    public function getForum_category_id()
    {
        return $this->forum_category_id;
    }

    public function getForum_name()
    {
        return $this->forum_name;
    }

    public function getForum_description()
    {
        return $this->forum_description;
    }

    public function getForum_active()
    {
        return $this->forum_active;
    }

    public function getForum_parent_id()
    {
        return $this->forum_parent_id;
    }

    public function getForum_order()
    {
        return $this->forum_order;
    }

    public function getForum_thank()
    {
        return $this->forum_thank;
    }

    public function getForum_post_count()
    {
        return $this->forum_post_count;
    }

    public function getForum_topic_count()
    {
        return $this->forum_topic_count;
    }

    public function getForum_post_add()
    {
        return $this->forum_post_add;
    }

    public function getForum_post_delete()
    {
        return $this->forum_post_delete;
    }

    public function getForum_post_update()
    {
        return $this->forum_post_update;
    }

    public function getForum_topic_add()
    {
        return $this->forum_topic_add;
    }

    public function getForum_topic_update()
    {
        return $this->forum_topic_update;
    }

    public function getForum_topic_delete()
    {
        return $this->forum_topic_delete;
    }
    
    public function getForum_fast_reply()
    {
        return $this->forum_fast_reply;
    }    

    public function getForum_rules()
    {
        return $this->forum_rules;
    }

    public function getForum_left()
    {
        return $this->forum_left;
    }

    public function getForum_right()
    {
        return $this->forum_right;
    }

    public function setForum_id($forum_id)
    {
        $this->forum_id = self::makeInt($forum_id);
        return $this;
    }

    public function setForum_category_id($forum_category_id)
    {
        $this->forum_category_id = self::makeInt($forum_category_id);
        return $this;
    }

    public function setForum_name($forum_name)
    {
        $this->forum_name = $forum_name;
        return $this;
    }

    public function setForum_description($forum_description)
    {
        $this->forum_description = $forum_description;
        return $this;
    }

    public function setForum_active($forum_active)
    {
        $this->forum_active = self::makeBool($forum_active);
        return $this;
    }

    public function setForum_parent_id($forum_parent_id)
    {
        $this->forum_parent_id = self::makeInt($forum_parent_id);
        return $this;
    }

    public function setForum_order($forum_order)
    {
        $this->forum_order = self::makeInt($forum_order);
        return $this;
    }

    public function setForum_thank($forum_thank)
    {
        $this->forum_thank = self::makeBool($forum_thank);
        return $this;
    }

    public function setForum_post_count($forum_post_count)
    {
        $this->forum_post_count = self::makeInt($forum_post_count);
        return $this;
    }

    public function setForum_topic_count($forum_topic_count)
    {
        $this->forum_topic_count = self::makeInt($forum_topic_count);
        return $this;
    }

    public function setForum_post_add($forum_post_add)
    {
        $this->forum_post_add = self::makeBool($forum_post_add);
        return $this;
    }

    public function setForum_post_delete($forum_post_delete)
    {
        $this->forum_post_delete = self::makeBool($forum_post_delete);
        return $this;
    }

    public function setForum_post_update($forum_post_update)
    {
        $this->forum_post_update = self::makeBool($forum_post_update);
        return $this;
    }

    public function setForum_topic_add($forum_topic_add)
    {
        $this->forum_topic_add = self::makeBool($forum_topic_add);
        return $this;
    }

    public function setForum_topic_update($forum_topic_update)
    {
        $this->forum_topic_update = self::makeBool($forum_topic_update);
        return $this;
    }

    public function setForum_topic_delete($forum_topic_delete)
    {
        $this->forum_topic_delete = self::makeBool($forum_topic_delete);
        return $this;
    }

    public function setForum_fast_reply($forum_fast_reply)
    {
        $this->forum_fast_reply = $forum_fast_reply;
        return $this;
    }
    
    public function setForum_rules($forum_rules)
    {
        $this->forum_rules = $forum_rules;
        return $this;
    }

    public function setForum_left($forum_left)
    {
        $this->forum_left = self::makeInt($forum_left);
        return $this;
    }

    public function setForum_right($forum_right)
    {
        $this->forum_right = self::makeInt($forum_right);
        return $this;
    }
    
    /**
     *
     * @param Row $values
     *
     * @return ForumEntity
     */
    public static function setFromRow(Row $values)
    {
        $forum = new ForumEntity();
        
        if (isset($values->forum_id)) {
            $forum->setForum_id($values->forum_id);
        }
        
        if (isset($values->forum_category_id)) {
            $forum->setForum_category_id($values->forum_category_id);
        }
        
        if (isset($values->forum_name)) {
            $forum->setForum_name($values->forum_name);
        }

        if (isset($values->forum_description)) {
            $forum->setForum_description($values->forum_description);
        }

        if (isset($values->forum_active)) {
            $forum->setForum_active($values->forum_active);
        }

        if (isset($values->forum_parent_id)) {
            $forum->setForum_parent_id($values->forum_parent_id);
        }

        if (isset($values->forum_order)) {
            $forum->setForum_order($values->forum_order);
        }

        if (isset($values->forum_thank)) {
            $forum->setForum_thank($values->forum_thank);
        }

        if (isset($values->forum_post_count)) {
            $forum->setForum_post_count($values->forum_post_count);
        }

        if (isset($values->forum_topic_count)) {
            $forum->setForum_topic_count($values->forum_topic_count);
        }

        if (isset($values->forum_post_add)) {
            $forum->setForum_post_add($values->forum_post_add);
        }

        if (isset($values->forum_post_delete)) {
            $forum->setForum_post_delete($values->forum_post_delete);
        }

        if (isset($values->forum_post_update)) {
            $forum->setForum_post_update($values->forum_post_update);
        }

        if (isset($values->forum_topic_add)) {
            $forum->setForum_topic_add($values->forum_topic_add);
        }

        if (isset($values->forum_topic_update)) {
            $forum->setForum_topic_update($values->forum_topic_update);
        }

        if (isset($values->forum_topic_delete)) {
            $forum->setForum_topic_delete($values->forum_topic_delete);
        }
        
        if (isset($values->forum_fast_reply)) {
            $forum->setForum_fast_reply($values->forum_fast_reply);
        }
        
        if (isset($values->forum_rules)) {
            $forum->setForum_rules($values->forum_rules);
        }

        if (isset($values->forum_left)) {
            $forum->setForum_left($values->forum_left);
        }
        
        if (isset($values->forum_right)) {
            $forum->setForum_right($values->forum_left);
        }
        
        return $forum;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return ForumEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $forumEntity = new ForumEntity();
        
        if (isset($values->forum_id)) {
            $forumEntity->setForum_id($values->forum_id);
        }
        
        if (isset($values->forum_category_id)) {
            $forumEntity->setForum_category_id($values->forum_category_id);
        }
        
        if (isset($values->forum_name)) {
            $forumEntity->setForum_name($values->forum_name);
        }

        if (isset($values->forum_description)) {
            $forumEntity->setForum_description($values->forum_description);
        }

        if (isset($values->forum_active)) {
            $forumEntity->setForum_active($values->forum_active);
        }

        if (isset($values->forum_parent_id)) {
            $forumEntity->setForum_parent_id($values->forum_parent_id);
        }

        if (isset($values->forum_order)) {
            $forumEntity->setForum_order($values->forum_order);
        }

        if (isset($values->forum_thank)) {
            $forumEntity->setForum_thank($values->forum_thank);
        }

        if (isset($values->forum_post_count)) {
            $forumEntity->setForum_post_count($values->forum_post_count);
        }

        if (isset($values->forum_topic_count)) {
            $forumEntity->setForum_topic_count($values->forum_topic_count);
        }

        if (isset($values->forum_post_add)) {
            $forumEntity->setForum_post_add($values->forum_post_add);
        }

        if (isset($values->forum_post_delete)) {
            $forumEntity->setForum_post_delete($values->forum_post_delete);
        }

        if (isset($values->forum_post_update)) {
            $forumEntity->setForum_post_update($values->forum_post_update);
        }

        if (isset($values->forum_topic_add)) {
            $forumEntity->setForum_topic_add($values->forum_topic_add);
        }

        if (isset($values->forum_topic_update)) {
            $forumEntity->setForum_topic_update($values->forum_topic_update);
        }

        if (isset($values->forum_topic_delete)) {
            $forumEntity->setForum_topic_delete($values->forum_topic_delete);
        }
        
        if (isset($values->forum_fast_reply)) {
            $forumEntity->setForum_fast_reply($values->forum_fast_reply);
        }
        
        if (isset($values->forum_rules)) {
            $forumEntity->setForum_rules($values->forum_rules);
        }

        if (isset($values->forum_left)) {
            $forumEntity->setForum_left($values->forum_left);
        }
        
        if (isset($values->forum_right)) {
            $forumEntity->setForum_right($values->forum_left);
        }
        
        return $forumEntity;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->forum_id)) {
            $res['forum_id'] = $this->forum_id;
        }
        
        if (isset($this->forum_category_id)) {
            $res['forum_category_id'] = $this->forum_category_id;
        }
        
        if (isset($this->forum_name)) {
            $res['forum_name'] = $this->forum_name;
        }
        
        if (isset($this->forum_description)) {
            $res['forum_description'] = $this->forum_description;
        }

        if (isset($this->forum_active)) {
            $res['forum_active'] = $this->forum_active;
        }

        if (isset($this->forum_parent_id)) {
            $res['forum_parent_id'] = $this->forum_parent_id;
        }

        if (isset($this->forum_order)) {
            $res['forum_order'] = $this->forum_order;
        }

        if (isset($this->forum_thank)) {
            $res['forum_thank'] = $this->forum_thank;
        }

        if (isset($this->forum_post_count)) {
            $res['forum_post_count'] = $this->forum_post_count;
        }

        if (isset($this->forum_topic_count)) {
            $res['forum_topic_count'] = $this->forum_topic_count;
        }
        
        if (isset($this->forum_post_add)) {
            $res['forum_post_add'] = $this->forum_post_add;
        }
        
        if (isset($this->forum_post_delete)) {
            $res['forum_post_delete'] = $this->forum_post_delete;
        }

        if (isset($this->forum_post_update)) {
            $res['forum_post_update'] = $this->forum_post_update;
        }

        if (isset($this->forum_topic_add)) {
            $res['forum_topic_add'] = $this->forum_topic_add;
        }

        if (isset($this->forum_topic_update)) {
            $res['forum_topic_update'] = $this->forum_topic_update;
        }

        if (isset($this->forum_topic_delete)) {
            $res['forum_topic_delete'] = $this->forum_topic_delete;
        }
        
        if (isset($this->forum_fast_reply)) {
            $res['setForum_fast_reply'] = $values->forum_fast_reply;
        }

        if (isset($this->forum_rules)) {
            $res['forum_rules'] = $this->forum_rules;
        }
        
        if (isset($this->forum_left)) {
            $res['forum_left'] = $this->forum_left;
        }
        
        if (isset($this->forum_right)) {
            $res['forum_right'] = $this->forum_right;
        }
        
        return $res;
    }
}
