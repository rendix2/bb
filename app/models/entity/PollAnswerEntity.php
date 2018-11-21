<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of PollAnswerEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class PollAnswerEntity extends Entity
{
    
    /**
     *
     * @var int $poll_answer_id
     */
    private $poll_answer_id;
    
    /**
     *
     * @var int $poll_id
     */
    private $poll_id;
    
    /**
     *
     * @var string $poll_answer
     */
    private $poll_answer;

    public function getPoll_answer_id()
    {
        return $this->poll_answer_id;
    }

    public function getPoll_id()
    {
        return $this->poll_id;
    }

    public function getPoll_answer()
    {
        return $this->poll_answer;
    }

    public function setPoll_answer_id($poll_answer_id)
    {
        $this->poll_answer_id = self::makeInt($poll_answer_id);
        return $this;
    }

    public function setPoll_id($poll_id)
    {
        $this->poll_id = self::makeInt($poll_id);
        return $this;
    }

    public function setPoll_answer($poll_answer)
    {
        $this->poll_answer = $poll_answer;
        return $this;
    }

    /**
     *
     * @param Row $values
     *
     * @return PollAnswerEntity
     */
    public static function setFromRow(Row $values)
    {
        $pollAnswerEntity = new PollAnswerEntity();
        
        if (isset($values->poll_answer_id)) {
            $pollAnswerEntity->setPoll_answer_id($values->poll_answer_id);
        }
        
        if (isset($values->poll_id)) {
            $pollAnswerEntity->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer)) {
            $pollAnswerEntity->setPoll_answer($values->poll_answer);
        }
        
        return $pollAnswerEntity;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return PollAnswerEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $pollAnswerEntity = new PollAnswerEntity();
        
        if (isset($values->poll_answer_id)) {
            $pollAnswerEntity->setPoll_answer_id($values->poll_answer_id);
        }
        
        if (isset($values->poll_id)) {
            $pollAnswerEntity->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer)) {
            $pollAnswerEntity->setPoll_answer($values->poll_answer);
        }
        
        return $pollAnswerEntity;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->poll_answer_id)) {
            $res['poll_answer_id'] = $this->poll_answer_id;
        }
        
        if (isset($this->poll_id)) {
            $res['poll_id'] = $this->poll_id;
        }

        if (isset($this->poll_answer)) {
            $res['poll_answer'] = $this->poll_answer;
        }
     
        return $res;
    }
}
