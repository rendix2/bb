<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of PollAnswer
 *
 * @author rendix2
 */
class PollAnswer extends Entity
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
     * @return PollAnswer
     */
    public static function setFromRow(Row $values)
    {
        $pollAnswer = new PollAnswer();
        
        if (isset($values->poll_answer_id)) {
            $pollAnswer->setPoll_answer_id($values->poll_answer_id);
        }
        
        if (isset($values->poll_id)) {
            $pollAnswer->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer)) {
            $pollAnswer->setPoll_answer($values->poll_answer);
        }        
        
        return $pollAnswer;
    }
    
    /**
     * 
     * @param Row $values
     * 
     * @return PollAnswer
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $pollAnswer = new PollAnswer();
        
        if (isset($values->poll_answer_id)) {
            $pollAnswer->setPoll_answer_id($values->poll_answer_id);
        }
        
        if (isset($values->poll_id)) {
            $pollAnswer->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer)) {
            $pollAnswer->setPoll_answer($values->poll_answer);
        }        
        
        return $pollAnswer;
    }    

    /**
     * 
     * @return [] 
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
