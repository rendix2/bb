<?php

namespace App\Models\Entity;

use Dibi\Row;
use Nette\Neon\Entity;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicWatchEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class TopicWatchEntity extends Entity
{
    /**
     *
     * @var int $id
     */
    private $id;
    
    /**
     *
     * @var int $topic_id
     */
    private $topic_id;
    
    /**
     *
     * @var int $user_id
     */
    private $user_id;

    public function getId()
    {
        return $this->id;
    }

    public function getTopic_id()
    {
        return $this->topic_id;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setId($id)
    {
        $this->id = self::makeInt($id);
        return $this;
    }

    public function setTopic_id($topic_id)
    {
        $this->topic_id = self::makeInt($topic_id);
        return $this;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = self::makeInt($user_id);
        return $this;
    }
    
    /**
     *
     * @param Row $values
     *
     * @return TopicWatchEntity
     */
    public static function setFromRow(Row $values)
    {
        $topicWatchEntity = new TopicWatchEntity();
        $topicWatchEntity->setId($values->id)
                   ->setTopic_id($values->topic_id)
                   ->setUser_id($values->user_id);
        
        return $topicWatchEntity;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return TopicWatchEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $topicWatchEntity = new TopicWatchEntity();
        $topicWatchEntity->setId($values->id)
                   ->setTopic_id($values->topic_id)
                   ->setUser_id($values->user_id);
        
        return $topicWatchEntity;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->id)) {
            $res['id'] = $this->id;
        }
        
        if (isset($this->topic_id)) {
            $res['topic_id'] = $this->topic_id;
        }

        if (isset($this->user_id)) {
            $res['user_id'] = $this->user_id;
        }
        
        return $res;
    }
}
