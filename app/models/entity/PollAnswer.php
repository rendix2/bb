<?php

namespace App\Models\Entity;

/**
 * Description of PollAnswer
 *
 * @author rendix2
 */
class PollAnswer extends Base\Entity
{
    
    /**
     *
     * @var int $poll_answer_id
     */
    public $poll_answer_id;
    
    /**
     *
     * @var int $poll_id
     */
    public $poll_id;
    
    /**
     *
     * @var string $poll_answer
     */
    public $poll_answer;

    
    public function __construct($poll_answer_id, $poll_id, $poll_answer)
    {
        $this->poll_answer_id = $poll_answer_id;
        $this->poll_id        = $poll_id;
        $this->poll_answer    = $poll_answer;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Post
     */
    public static function setFromDibi(\Dibi\Row $values)
    {
        return new PollAnswer(
            $values->poll_answer_id,
            $values->poll_id,
            $values->poll_answer_id
        );
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Post
     */
    public static function setFromArrayHash(\Nette\Utils\ArrayHash $values)
    {
        return new PollAnswer(
            isset($values->poll_answer_id) ? $values->poll_answer_id : null,
            isset($values->poll_id)        ? $values->poll_id        : null,
            isset($values->poll_answer)    ? $values->poll_answer    : null
        );
    }    

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
