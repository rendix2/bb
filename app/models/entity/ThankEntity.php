<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of ThankEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class ThankEntity extends Entity
{
    /**
     *
     * @var int $thank_id
     */
    private $thank_id;
    
    /**
     *
     * @var int $thank_forum_id
     */
    private $thank_forum_id;
    
    /**
     *
     * @var int $thank_topic_id
     */
    private $thank_topic_id;
    
    /**
     *
     * @var int $thank_user_id
     */
    private $thank_user_id;
    
    /**
     *
     * @var int $thank_time
     */
    private $thank_time;
    
    /**
     *
     * @var string $thank_user_ip
     */
    private $thank_user_ip;

    /**
     * @return int
     */
    public function getThank_id()
    {
        return $this->thank_id;
    }

    /**
     * @return int
     */
    public function getThank_forum_id()
    {
        return $this->thank_forum_id;
    }

    /**
     * @return int
     */
    public function getThank_topic_id()
    {
        return $this->thank_topic_id;
    }

    /**
     * @return int
     */
    public function getThank_user_id()
    {
        return $this->thank_user_id;
    }

    /**
     * @return int
     */
    public function getThank_time()
    {
        return $this->thank_time;
    }

    /**
     * @return string
     */
    public function getThank_user_ip()
    {
        return $this->thank_user_ip;
    }

    /**
     * @param $thank_id
     *
     * @return ThankEntity
     */
    public function setThank_id($thank_id)
    {
        $this->thank_id = $thank_id;
        return $this;
    }

    /**
     * @param $thank_forum_id
     *
     * @return ThankEntity
     */
    public function setThank_forum_id($thank_forum_id)
    {
        $this->thank_forum_id = self::makeInt($thank_forum_id);
        return $this;
    }

    /**
     * @param $thank_topic_id
     *
     * @return ThankEntity
     */
    public function setThank_topic_id($thank_topic_id)
    {
        $this->thank_topic_id = self::makeInt($thank_topic_id);
        return $this;
    }

    /**
     * @param $thank_user_id
     *
     * @return ThankEntity
     */
    public function setThank_user_id($thank_user_id)
    {
        $this->thank_user_id = self::makeInt($thank_user_id);
        return $this;
    }

    /**
     * @param $thank_time
     *
     * @return ThankEntity
     */
    public function setThank_time($thank_time)
    {
        $this->thank_time = self::makeInt($thank_time);
        return $this;
    }

    /**
     * @param $thank_user_ip
     *
     * @return ThankEntity
     */
    public function setThank_user_ip($thank_user_ip)
    {
        $this->thank_user_ip = $thank_user_ip;
        return $this;
    }
    
    /**
     *
     * @param Row $values
     *
     * @return ThankEntity
     */
    public static function setFromRow(Row $values)
    {
        $thankEntity = new ThankEntity();
        
        if (isset($values->thank_id)) {
            $thankEntity->setThank_id($values->thank_id);
        }
        
        if (isset($values->thank_forum_id)) {
            $thankEntity->setThank_forum_id($values->thank_forum_id);
        }
        
        if (isset($values->thank_topic_id)) {
            $thankEntity->setThank_topic_id($values->thank_topic_id);
        }
        
        if (isset($values->thank_user_id)) {
            $thankEntity->setThank_user_id($values->thank_user_id);
        }
        
        if (isset($values->thank_time)) {
            $thankEntity->setThank_time($values->thank_time);
        }
        
        if (isset($values->thank_user_ip)) {
            $thankEntity->setThank_user_ip($values->thank_user_ip);
        }

        return $thankEntity;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return ThankEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $thankEntity = new ThankEntity();
        
        if (isset($values->thank_id)) {
            $thankEntity->setThank_id($values->thank_id);
        }
        
        if (isset($values->thank_forum_id)) {
            $thankEntity->setThank_forum_id($values->thank_forum_id);
        }
        
        if (isset($values->thank_topic_id)) {
            $thankEntity->setThank_topic_id($values->thank_topic_id);
        }
        
        if (isset($values->thank_user_id)) {
            $thankEntity->setThank_user_id($values->thank_user_id);
        }
        
        if (isset($values->thank_time)) {
            $thankEntity->setThank_time($values->thank_time);
        }
        
        if (isset($values->thank_user_ip)) {
            $thankEntity->setThank_user_ip($values->thank_user_ip);
        }

        return $thankEntity;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->thank_id)) {
            $res['thank_id'] = $this->thank_id;
        }
        
        if (isset($this->thank_forum_id)) {
            $res['thank_forum_id'] = $this->thank_forum_id;
        }

        if (isset($this->thank_topic_id)) {
            $res['thank_topic_id'] = $this->thank_topic_id;
        }

        if (isset($this->thank_user_id)) {
            $res['thank_user_id'] = $this->thank_user_id;
        }

        if (isset($this->thank_time)) {
            $res['thank_time'] = $this->thank_time;
        }

        if (isset($this->thank_user_ip)) {
            $res['thank_user_ip'] = $this->thank_user_ip;
        }
        
        return $res;
    }
}
