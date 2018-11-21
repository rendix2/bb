<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
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

    public function getTopic_id()
    {
        return $this->topic_id;
    }

    public function getTopic_category_id()
    {
        return $this->topic_category_id;
    }

    public function getTopic_forum_id()
    {
        return $this->topic_forum_id;
    }

    public function getTopic_user_id()
    {
        return $this->topic_user_id;
    }

    public function getTopic_name()
    {
        return $this->topic_name;
    }

    public function getTopic_post_count()
    {
        return $this->topic_post_count;
    }

    public function getTopic_add_time()
    {
        return $this->topic_add_time;
    }

    public function getTopic_locked()
    {
        return $this->topic_locked;
    }

    public function getTopic_view_count()
    {
        return $this->topic_view_count;
    }

    public function getTopic_first_post_id()
    {
        return $this->topic_first_post_id;
    }

    public function getTopic_first_user_id()
    {
        return $this->topic_first_user_id;
    }

    public function getTopic_last_post_id()
    {
        return $this->topic_last_post_id;
    }

    public function getTopic_last_user_id()
    {
        return $this->topic_last_user_id;
    }

    public function getTopic_order()
    {
        return $this->topic_order;
    }

    public function getTopic_page_count()
    {
        return $this->topic_page_count;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getPoll()
    {
        return $this->poll;
    }

    public function setTopic_id($topic_id)
    {
        $this->topic_id = self::makeInt($topic_id);
        return $this;
    }

    public function setTopic_category_id($topic_category_id)
    {
        $this->topic_category_id = self::makeInt($topic_category_id);
        return $this;
    }

    public function setTopic_forum_id($topic_forum_id)
    {
        $this->topic_forum_id = self::makeInt($topic_forum_id);
        return $this;
    }

    public function setTopic_user_id($topic_user_id)
    {
        $this->topic_user_id = self::makeInt($topic_user_id);
        return $this;
    }

    public function setTopic_name($topic_name)
    {
        $this->topic_name = $topic_name;
        return $this;
    }

    public function setTopic_post_count($topic_post_count)
    {
        $this->topic_post_count = self::makeInt($topic_post_count);
        return $this;
    }

    public function setTopic_add_time($topic_add_time)
    {
        $this->topic_add_time = self::makeInt($topic_add_time);
        return $this;
    }

    public function setTopic_locked($topic_locked)
    {
        $this->topic_locked = self::makeBool($topic_locked);
        return $this;
    }

    public function setTopic_view_count($topic_view_count)
    {
        $this->topic_view_count = self::makeInt($topic_view_count);
        return $this;
    }

    public function setTopic_first_post_id($topic_first_post_id)
    {
        $this->topic_first_post_id = self::makeInt($topic_first_post_id);
        return $this;
    }

    public function setTopic_first_user_id($topic_first_user_id)
    {
        $this->topic_first_user_id = self::makeInt($topic_first_user_id);
        return $this;
    }

    public function setTopic_last_post_id($topic_last_post_id)
    {
        $this->topic_last_post_id = self::makeInt($topic_last_post_id);
        return $this;
    }

    public function setTopic_last_user_id($topic_last_user_id)
    {
        $this->topic_last_user_id = self::makeInt($topic_last_user_id);
        return $this;
    }

    public function setTopic_order($topic_order)
    {
        $this->topic_order = self::makeInt($topic_order);
        return $this;
    }

    public function setTopic_page_count($topic_page_count)
    {
        $this->topic_page_count = self::makeInt($topic_page_count);
        return $this;
    }

    public function setPost(PostEntity $post = null)
    {
        $this->post = $post;
        return $this;
    }

    public function setPoll(PollEntity $poll = null)
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

    public static function setFromArrayHash(ArrayHash $values)
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
            $topic->setTopic_page_count($values->topic_post_count);
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

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];

        if (isset($this->topic_id)) {
            $res['topic_id'] = $this->topic_id;
        }

        if (isset($this->topic_category_id)) {
            $res['topic_category_id'] = $this->topic_category_id;
        }

        if (isset($this->topic_forum_id)) {
            $res['topic_forum_id'] = $this->topic_forum_id;
        }

        if (isset($this->topic_user_id)) {
            $res['topic_user_id'] = $this->topic_user_id;
        }

        if (isset($this->topic_name)) {
            $res['topic_name'] = $this->topic_name;
        }

        if (isset($this->topic_post_count)) {
            $res['topic_post_count'] = $this->topic_post_count;
        }

        if (isset($this->topic_add_time)) {
            $res['topic_add_time'] = $this->topic_add_time;
        }

        if (isset($this->topic_locked)) {
            $res['topic_locked'] = $this->topic_locked;
        }

        if (isset($this->topic_view_count)) {
            $res['topic_view_count'] = $this->topic_view_count;
        }

        if (isset($this->topic_first_post_id)) {
            $res['topic_first_post_id'] = $this->topic_first_post_id;
        }

        if (isset($this->topic_first_user_id)) {
            $res['topic_first_user_id'] = $this->topic_first_user_id;
        }

        if (isset($this->topic_last_post_id)) {
            $res['topic_last_post_id'] = $this->topic_last_post_id;
        }

        if (isset($this->topic_last_user_id)) {
            $res['topic_last_user_id'] = $this->topic_last_user_id;
        }

        if (isset($this->topic_order)) {
            $res['topic_order'] = $this->topic_order;
        }

        if (isset($this->topic_page_count)) {
            $res['topic_page_count'] = $this->topic_page_count;
        }

        return $res;
    }
}
