<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of PollEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class PollEntity extends Entity
{
    /**
     *
     * @var int $poll_id
     */
    private $poll_id;
    
    /**
     *
     * @var int $poll_topic_id
     */
    private $poll_topic_id;
    
    /**
     *
     * @var string $poll_question
     */
    private $poll_question;
    
    /**
     *
     * @var int $poll_time_to
     */
    private $poll_time_to;
    
    /**
     *
     * @var PollAnswerEntity[] $pollAnswers
     */
    private $pollAnswers;

    public function getPoll_id()
    {
        return $this->poll_id;
    }

    public function getPoll_topic_id()
    {
        return $this->poll_topic_id;
    }

    public function getPoll_question()
    {
        return $this->poll_question;
    }

    public function getPoll_time_to()
    {
        return $this->poll_time_to;
    }

    public function getPollAnswers()
    {
        return $this->pollAnswers;
    }

    public function setPoll_id($poll_id)
    {
        $this->poll_id = self::makeInt($poll_id);
        return $this;
    }

    public function setPoll_topic_id($poll_topic_id)
    {
        $this->poll_topic_id = self::makeInt($poll_topic_id);
        return $this;
    }

    public function setPoll_question($poll_question)
    {
        $this->poll_question = $poll_question;
        return $this;
    }

    public function setPoll_time_to($poll_time_to)
    {
        $this->poll_time_to = self::makeTimestamp($poll_time_to);
        return $this;
    }

    public function setPollAnswers(array $pollAnswers = [])
    {
        $this->pollAnswers = $pollAnswers;
        return $this;
    }

    /**
     *
     * @param Row $values
     *
     * @return PollEntity
     */
    public static function setFromRow(Row $values)
    {
        $poll = new PollEntity();

        if (isset($values->poll_id)) {
            $poll->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_topic_id)) {
            $poll->setPoll_topic_id($values->poll_topic_id);
        }

        if (isset($values->poll_question)) {
            $poll->setPoll_question($values->poll_question);
        }

        if (isset($values->poll_time_to)) {
            $poll->setPoll_time_to($values->poll_time_to);
        }

        return $poll;
    }

    /**
     *
     * @param Row $values
     *
     * @return PollEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $poll = new PollEntity();

        if (isset($values->poll_id)) {
            $poll->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_topic_id)) {
            $poll->setPoll_topic_id($values->poll_topic_id);
        }

        if (isset($values->poll_question)) {
            $poll->setPoll_question($values->poll_question);
        }

        if (isset($values->poll_time_to)) {
            $poll->setPoll_time_to($values->poll_time_to);
        }

        return $poll;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $res = [];

        if (isset($this->poll_id)) {
            $res['poll_id'] = $this->poll_id;
        }

        if (isset($this->poll_topic_id)) {
            $res['poll_topic_id'] = $this->poll_topic_id;
        }

        if (isset($this->poll_question)) {
            $res['poll_question'] = $this->poll_question;
        }

        if (isset($this->poll_time_to)) {
            $res['poll_time_to'] = $this->poll_time_to;
        }

        return $res;
    }
}
