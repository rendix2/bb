<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Application\Attributes\Deprecated;
use Nette\Utils\ArrayHash;

/**
 * Description of PollEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
#[\JetBrains\PhpStorm\Deprecated]
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
     * @param $poll_id
     *
     * @return PollEntity
     */
    public function setPoll_id($poll_id)
    {
        $this->poll_id = self::makeInt($poll_id);
        return $this;
    }

    /**
     * @param $poll_topic_id
     *
     * @return PollEntity
     */
    public function setPoll_topic_id($poll_topic_id)
    {
        $this->poll_topic_id = self::makeInt($poll_topic_id);
        return $this;
    }

    /**
     * @param $poll_question
     *
     * @return PollEntity
     */
    public function setPoll_question($poll_question)
    {
        $this->poll_question = $poll_question;
        return $this;
    }

    /**
     * @param $poll_time_to
     *
     * @return PollEntity
     */
    public function setPoll_time_to($poll_time_to)
    {
        $this->poll_time_to = self::makeTimestamp($poll_time_to);
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


}
