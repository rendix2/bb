<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Application\Attributes\Deprecated;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
#[\JetBrains\PhpStorm\Deprecated]

class TopicEntity extends Entity
{
    /**
     *
     * @var int $topic_id
     */
    private $topic_id;

    /**
     *
     * @var int $topic_category_id
     */
    private $topic_category_id;

    /**
     *
     * @var int $topic_forum_id
     */
    private $topic_forum_id;

    /**
     *
     * @var int $topic_user_id
     */
    private $topic_user_id;

    /**
     *
     * @var string $topic_name
     */
    private $topic_name;

    /**
     *
     * @var int $topic_post_count
     */
    private $topic_post_count;

    /**
     * @var int $topic_add_time
     */
    private $topic_add_time;

    /**
     *
     * @var bool $topic_locked
     */
    private $topic_locked;

    /**
     *
     * @var int $topic_view_count
     */
    private $topic_view_count;

    /**
     *
     * @var int $topic_first_post_id
     */
    private $topic_first_post_id;

    /**
     *
     * @var int $topic_first_user_id
     */
    private $topic_first_user_id;

    /**
     *
     * @var int $topic_last_post_id
     */
    private $topic_last_post_id;

    /**
     *
     * @var int $topic_last_post_id
     */
    private $topic_last_user_id;

    /**
     *
     * @var int $topic_order
     */
    private $topic_order;

    /**
     *
     * @var int $topic_page_count
     */
    private $topic_page_count;

    /**
     *
     * @var PostEntity $post
     */
    private $post;

    /**
     *
     * @var PollEntity $poll
     */
    private $poll;

    /**
     * @return int
     */
    public function getTopic_id()
    {
        return $this->topic_id;
    }

    /**
     * @return int
     */
    public function getTopic_category_id()
    {
        return $this->topic_category_id;
    }

    /**
     * @return int
     */
    public function getTopic_forum_id()
    {
        return $this->topic_forum_id;
    }

    /**
     * @return int
     */
    public function getTopic_user_id()
    {
        return $this->topic_user_id;
    }

    /**
     * @return string
     */
    public function getTopic_name()
    {
        return $this->topic_name;
    }

    /**
     * @return int
     */
    public function getTopic_post_count()
    {
        return $this->topic_post_count;
    }

    /**
     * @return int
     */
    public function getTopic_add_time()
    {
        return $this->topic_add_time;
    }

    /**
     * @return bool
     */
    public function getTopic_locked()
    {
        return $this->topic_locked;
    }

    /**
     * @return int
     */
    public function getTopic_view_count()
    {
        return $this->topic_view_count;
    }

    /**
     * @return int
     */
    public function getTopic_first_post_id()
    {
        return $this->topic_first_post_id;
    }

    /**
     * @return int
     */
    public function getTopic_first_user_id()
    {
        return $this->topic_first_user_id;
    }

    /**
     * @return int
     */
    public function getTopic_last_post_id()
    {
        return $this->topic_last_post_id;
    }

    /**
     * @return int
     */
    public function getTopic_last_user_id()
    {
        return $this->topic_last_user_id;
    }

    /**
     * @return int
     */
    public function getTopic_order()
    {
        return $this->topic_order;
    }

    /**
     * @return int
     */
    public function getTopic_page_count()
    {
        return $this->topic_page_count;
    }

    /**
     * @return PostEntity
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return PollEntity
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param $topic_id
     *
     * @return TopicEntity
     */
    public function setTopic_id($topic_id)
    {
        $this->topic_id = self::makeInt($topic_id);
        return $this;
    }

    /**
     * @param $topic_category_id
     *
     * @return TopicEntity
     */
    public function setTopic_category_id($topic_category_id)
    {
        $this->topic_category_id = self::makeInt($topic_category_id);
        return $this;
    }

    /**
     * @param $topic_forum_id
     *
     * @return TopicEntity
     */
    public function setTopic_forum_id($topic_forum_id)
    {
        $this->topic_forum_id = self::makeInt($topic_forum_id);
        return $this;
    }

    /**
     * @param $topic_user_id
     *
     * @return TopicEntity
     */
    public function setTopic_user_id($topic_user_id)
    {
        $this->topic_user_id = self::makeInt($topic_user_id);
        return $this;
    }

    /**
     * @param $topic_name
     *
     * @return TopicEntity
     */
    public function setTopic_name($topic_name)
    {
        $this->topic_name = $topic_name;
        return $this;
    }

    /**
     * @param $topic_post_count
     *
     * @return TopicEntity
     */
    public function setTopic_post_count($topic_post_count)
    {
        $this->topic_post_count = self::makeInt($topic_post_count);
        return $this;
    }

    /**
     * @param $topic_add_time
     *
     * @return TopicEntity
     */
    public function setTopic_add_time($topic_add_time)
    {
        $this->topic_add_time = self::makeInt($topic_add_time);
        return $this;
    }

    /**
     * @param $topic_locked
     *
     * @return TopicEntity
     */
    public function setTopic_locked($topic_locked)
    {
        $this->topic_locked = self::makeBool($topic_locked);
        return $this;
    }

    /**
     * @param $topic_view_count
     *
     * @return TopicEntity
     */
    public function setTopic_view_count($topic_view_count)
    {
        $this->topic_view_count = self::makeInt($topic_view_count);
        return $this;
    }

    /**
     * @param $topic_first_post_id
     *
     * @return TopicEntity
     */
    public function setTopic_first_post_id($topic_first_post_id)
    {
        $this->topic_first_post_id = self::makeInt($topic_first_post_id);
        return $this;
    }

    /**
     * @param $topic_first_user_id
     *
     * @return TopicEntity
     */
    public function setTopic_first_user_id($topic_first_user_id)
    {
        $this->topic_first_user_id = self::makeInt($topic_first_user_id);
        return $this;
    }

    /**
     * @param $topic_last_post_id
     *
     * @return TopicEntity
     */
    public function setTopic_last_post_id($topic_last_post_id)
    {
        $this->topic_last_post_id = self::makeInt($topic_last_post_id);
        return $this;
    }

    /**
     * @param $topic_last_user_id
     *
     * @return TopicEntity
     */
    public function setTopic_last_user_id($topic_last_user_id)
    {
        $this->topic_last_user_id = self::makeInt($topic_last_user_id);
        return $this;
    }

    /**
     * @param $topic_order
     *
     * @return TopicEntity
     */
    public function setTopic_order($topic_order)
    {
        $this->topic_order = self::makeInt($topic_order);
        return $this;
    }

    /**
     * @param $topic_page_count
     *
     * @return TopicEntity
     */
    public function setTopic_page_count($topic_page_count)
    {
        $this->topic_page_count = self::makeInt($topic_page_count);
        return $this;
    }

    /**
     * @param PostEntity|null $post
     *
     * @return TopicEntity
     */
    public function setPost(?PostEntity $post = null)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @param PollEntity|null $poll
     *
     * @return TopicEntity
     */
    public function setPoll(?PollEntity $poll = null)
    {
        $this->poll = $poll;
        return $this;
    }

    /**
     *
     * @param Row $values
     *
     * @return TopicEntity
     */
    public static function setFromRow(Row $values)
    {
        $topic = new TopicEntity();

        if (isset($values->topic_id)) {
            $topic->setTopic_id($values->topic_id);
        }

        if (isset($values->topic_category_id)) {
            $topic->setTopic_category_id($values->topic_category_id);
        }

        if (isset($values->topic_forum_id)) {
            $topic->setTopic_forum_id($values->topic_forum_id);
        }

        if (isset($values->topic_user_id)) {
            $topic->setTopic_user_id($values->topic_user_id);
        }

        if (isset($values->topic_name)) {
            $topic->setTopic_name($values->topic_name);
        }

        if (isset($values->topic_post_count)) {
            $topic->setTopic_post_count($values->topic_post_count);
        }

        if (isset($values->topic_add_time)) {
            $topic->setTopic_add_time($values->topic_add_time);
        }

        if (isset($values->topic_locked)) {
            $topic->setTopic_locked($values->topic_locked);
        }

        if (isset($values->topic_view_count)) {
            $topic->setTopic_view_count($values->topic_view_count);
        }

        if (isset($values->topic_first_post_id)) {
            $topic->setTopic_first_post_id($values->topic_first_post_id);
        }

        if (isset($values->topic_first_user_id)) {
            $topic->setTopic_first_user_id($values->topic_first_user_id);
        }

        if (isset($values->topic_last_post_id)) {
            $topic->setTopic_last_post_id($values->topic_last_post_id);
        }

        if (isset($values->topic_last_user_id)) {
            $topic->setTopic_last_user_id($values->topic_last_user_id);
        }

        if (isset($values->topic_order)) {
            $topic->setTopic_order($values->topic_order);
        }

        if (isset($values->topic_page_count)) {
            $topic->setTopic_page_count($values->topic_page_count);
        }

        return $topic;
    }




}
