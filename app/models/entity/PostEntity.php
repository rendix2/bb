<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of PostEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class PostEntity extends Entity
{
    /**
     *
     * @var int $post_id
     */
    private $post_id;
    
    /**
     *
     * @var int $post_user_id
     */    
    private $post_user_id;

    /**
     *
     * @var int $post_category_id
     */
    private $post_category_id;
    
    /**
     *
     * @var int $post_forum_id 
     */    
    private $post_forum_id;

    /**
     *
     * @var int $post_topic_id
     */    
    private $post_topic_id;
   
    /**
     *
     * @var string $post_title 
     */    
    private $post_title;
    
    /**
     *
     * @var string $post_text
     */    
    private $post_text;
    
    /**
     *
     * @var int $post_add_time
     */    
    private $post_add_time;
    
    /**
     *
     * @var string $post_add_user_ip 
     */    
    private $post_add_user_ip;
    
    /**
     *
     * @var string $post_edit_user_ip
     */    
    private $post_edit_user_ip;
    
    /**
     *
     * @var int $post_edit_count
     */
    private $post_edit_count;
    
    /**
     *
     * @var int $post_last_edit_time
     */    
    private $post_last_edit_time;
    
    /**
     *
     * @var int $post_locked
     */    
    private $post_locked;
    
    /**
     *
     * @var int $post_order
     */    
    private $post_order;
    
    /**
     *
     * @var FileEntity[] $files
     */
    private $post_files;
        
    public function getPost_id()
    {
        return $this->post_id;
    }

    public function getPost_user_id()
    {
        return $this->post_user_id;
    }

    public function getPost_category_id()
    {
        return $this->post_category_id;
    }

    public function getPost_forum_id()
    {
        return $this->post_forum_id;
    }

    public function getPost_topic_id()
    {
        return $this->post_topic_id;
    }

    public function getPost_title()
    {
        return $this->post_title;
    }

    public function getPost_text()
    {
        return $this->post_text;
    }

    public function getPost_add_time()
    {
        return $this->post_add_time;
    }

    public function getPost_add_user_ip()
    {
        return $this->post_add_user_ip;
    }

    public function getPost_edit_user_ip()
    {
        return $this->post_edit_user_ip;
    }

    public function getPost_edit_count()
    {
        return $this->post_edit_count;
    }

    public function getPost_last_edit_time()
    {
        return $this->post_last_edit_time;
    }

    public function getPost_locked()
    {
        return $this->post_locked;
    }

    public function getPost_order()
    {
        return $this->post_order;
    }

    public function getPost_files()
    {
        return $this->post_files;
    }
    
    public function setPost_id($post_id)
    {
        $this->post_id = self::makeInt($post_id);
        return $this;
    }

    public function setPost_user_id($post_user_id)
    {
        $this->post_user_id = self::makeInt($post_user_id);
        return $this;
    }

    public function setPost_category_id($post_category_id)
    {
        $this->post_category_id = self::makeInt($post_category_id);
        return $this;
    }

    public function setPost_forum_id($post_forum_id)
    {
        $this->post_forum_id = self::makeInt($post_forum_id);
        return $this;
    }

    public function setPost_topic_id($post_topic_id)
    {
        $this->post_topic_id = self::makeInt($post_topic_id);
        return $this;
    }

    public function setPost_title($post_title)
    {
        $this->post_title = $post_title;
        return $this;
    }

    public function setPost_text($post_text)
    {
        $this->post_text = $post_text;
        return $this;
    }

    public function setPost_add_time($post_add_time)
    {
        $this->post_add_time = self::makeInt($post_add_time);
        return $this;
    }

    public function setPost_add_user_ip($post_add_user_ip)
    {
        $this->post_add_user_ip = $post_add_user_ip;
        return $this;
    }

    public function setPost_edit_user_ip($post_edit_user_ip)
    {
        $this->post_edit_user_ip = $post_edit_user_ip;
        return $this;
    }

    public function setPost_edit_count($post_edit_count)
    {
        $this->post_edit_count = self::makeInt($post_edit_count);
        return $this;
    }

    public function setPost_last_edit_time($post_last_edit_time)
    {
        $this->post_last_edit_time = self::makeInt($post_last_edit_time);
        return $this;
    }

    public function setPost_locked($post_locked)
    {
        $this->post_locked = self::makeBool($post_locked);
        return $this;
    }

    public function setPost_order($post_order)
    {
        $this->post_order = self::makeInt($post_order);
        return $this;
    }
    
    public function setPost_files(array $files = [])
    {
        $this->post_files = $files;
        return $this;
    }    

    /**
     * 
     * @param Row $values
     * 
     * @return PostEntity
     */
    public static function setFromRow(Row $values)
    {
        $post = new PostEntity();
        
        if (isset($values->post_id)) {
            $post->setPost_id($values->post_id);
        }
        
        if (isset($values->post_user_id)) {
            $post->setPost_user_id($values->post_user_id);
        }

        if (isset($values->post_category_id)) {
            $post->setPost_category_id($values->post_category_id);
        }

        if (isset($values->post_forum_id)) {
            $post->setPost_forum_id($values->post_forum_id);
        }

        if (isset($values->post_topic_id)) {
            $post->setPost_topic_id($values->post_topic_id);
        }

        if (isset($values->post_title)) {
            $post->setPost_title($values->post_title);
        }
        
        if (isset($values->post_text)) {
            $post->setPost_text($values->post_text);
        }
        
        if (isset($values->post_add_time)) {
            $post->setPost_add_time($values->post_add_time);
        }

        if (isset($values->post_add_user_ip)) {
            $post->setPost_add_user_ip($values->post_add_user_ip);
        }

        if (isset($values->post_edit_user_ip)) {
            $post->setPost_edit_user_ip($values->post_edit_user_ip);
        }

        if (isset($values->post_edit_count)) {
            $post->setPost_edit_count($values->post_edit_count);
        }
        
        if (isset($values->post_last_edit_time)) {
            $post->setPost_last_edit_time($values->post_last_edit_time);
        }

        if (isset($values->post_locked)) {
            $post->setPost_locked($values->post_locked);
        }

        if (isset($values->post_order)) {
            $post->setPost_order($values->post_order);
        }
      
        return $post;
    }
    
    /**
     * 
     * @param ArrayHash $values
     * 
     * @return PostEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $post = new PostEntity();
        
        if (isset($values->post_id)) {
            $post->setPost_id($values->post_id);
        }
        
        if (isset($values->post_user_id)) {
            $post->setPost_user_id($values->post_user_id);
        }

        if (isset($values->post_category_id)) {
            $post->setPost_category_id($values->post_category_id);
        }

        if (isset($values->post_forum_id)) {
            $post->setPost_forum_id($values->post_forum_id);
        }

        if (isset($values->post_topic_id)) {
            $post->setPost_topic_id($values->post_topic_id);
        }

        if (isset($values->post_title)) {
            $post->setPost_title($values->post_title);
        }
        
        if (isset($values->post_text)) {
            $post->setPost_text($values->post_text);
        }
        
        if (isset($values->post_add_time)) {
            $post->setPost_add_time($values->post_add_time);
        }

        if (isset($values->post_add_user_ip)) {
            $post->setPost_add_user_ip($values->post_add_user_ip);
        }

        if (isset($values->post_edit_user_ip)) {
            $post->setPost_edit_user_ip($values->post_edit_user_ip);
        }

        if (isset($values->post_edit_count)) {
            $post->setPost_edit_count($values->post_edit_count);
        }
        
        if (isset($values->post_last_edit_time)) {
            $post->setPost_last_edit_time($values->post_last_edit_time);
        }

        if (isset($values->post_locked)) {
            $post->setPost_locked($values->post_locked);
        }

        if (isset($values->post_order)) {
            $post->setPost_order($values->post_order);
        }
      
        return $post;        
    }

    public function getArray()
    {
        $res = [];
        
        if (isset($this->post_id)) {
            $res['post_id'] = $this->post_id;
        }
        
        if (isset($this->post_user_id)) {
            $res['post_user_id'] = $this->post_user_id;
        }
        
        if (isset($this->post_category_id)) {
            $res['post_category_id'] = $this->post_category_id;
        }

        if (isset($this->post_forum_id)) {
            $res['post_forum_id'] = $this->post_forum_id;
        }

        if (isset($this->post_topic_id)) {
            $res['post_topic_id'] = $this->post_topic_id;
        }

        if (isset($this->post_title)) {
            $res['post_title'] = $this->post_title;
        }

        if (isset($this->post_text)) {
            $res['post_text'] = $this->post_text;
        }

        if (isset($this->post_add_time)) {
            $res['post_add_time'] = $this->post_add_time;
        }

        if (isset($this->post_add_user_ip)) {
            $res['post_add_user_ip'] = $this->post_add_user_ip;
        }

        if (isset($this->post_edit_count)) {
            $res['post_edit_count'] = $this->post_edit_count;
        }

        if (isset($this->post_last_edit_time)) {
            $res['post_last_edit_time'] = $this->post_last_edit_time;
        }

        if (isset($this->post_locked)) {
            $res['post_locked'] = $this->post_locked;
        }
        
        if (isset($this->post_order)) {
            $res['post_order'] = $this->post_order;
        }        
                
        return $res;
    }
}