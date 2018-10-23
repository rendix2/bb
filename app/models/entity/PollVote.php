<?php

namespace App\Models\Entity;

/**
 * Description of PollVote
 *
 * @author rendix2
 */
class PollVote extends Base\Entity
{
     /**
     *
     * @var int $poll_vote_id
     */
    public $poll_vote_id;
    
    /**
     *
     * @var int $poll_id
     */
    public $poll_id;
    
    /**
     *
     * @var int $poll_answer
     */
    public $poll_answer_id;
    
    /**
     *
     * @var int $poll_user_id
     */
    public $poll_user_id;

    /**
     * 
     * @param int $poll_vote_id
     * @param int $poll_id
     * @param int $poll_answer_id
     * @param int $poll_user_id
     * 
     */
    public function __construct($poll_vote_id, $poll_id, $poll_answer_id, $poll_user_id)
    {
        $this->poll_vote_id   = $poll_vote_id;
        $this->poll_id        = $poll_id;
        $this->poll_answer_id = $poll_answer_id;
        $this->poll_user_id   = $poll_user_id;
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
            $values->poll_vote_id,
            $values->poll_id,
            $values->poll_answer_id,
            $values->poll_user_id
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
            isset($values->poll_vote_id) ? $values->poll_vote_id : null,
            isset($values->poll_id)      ? $values->poll_id      : null,
            isset($values->poll_answer)  ? $values->poll_answer  : null,
            isset($values->poll_user_id) ? $values->poll_user_id : null
        );
    }    

    public function getArray()
    {
        $res = [];
        
        if (isset($this->poll_vote_id)) {
            $res['poll_vote_id'] = $this->poll_vote_id;
        }
        
        if (isset($this->poll_id)) {
            $res['poll_id'] = $this->poll_id;
        }

        if (isset($this->poll_answer_id)) {
            $res['poll_answer_id'] = $this->poll_answer_id;
        }
        
        if (isset($this->poll_user_id)) {
            $res['poll_user_id'] = $this->poll_user_id;
        }            
     
        return $res;
    }
}
