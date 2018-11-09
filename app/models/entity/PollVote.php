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
    private $poll_vote_id;
    
    /**
     *
     * @var int $poll_id
     */
    private $poll_id;
    
    /**
     *
     * @var int $poll_answer
     */
    private $poll_answer_id;
    
    /**
     *
     * @var int $poll_user_id
     */
    private $poll_user_id;

    public function getPoll_vote_id()
    {
        return $this->poll_vote_id;
    }

    public function getPoll_id()
    {
        return $this->poll_id;
    }

    public function getPoll_answer_id()
    {
        return $this->poll_answer_id;
    }

    public function getPoll_user_id()
    {
        return $this->poll_user_id;
    }

    public function setPoll_vote_id($poll_vote_id)
    {
        $this->poll_vote_id = self::makeInt($poll_vote_id);
        return $this;
    }

    public function setPoll_id($poll_id)
    {
        $this->poll_id = self::makeInt($poll_id);
        return $this;
    }

    public function setPoll_answer_id($poll_answer_id)
    {
        $this->poll_answer_id = self::makeInt($poll_answer_id);
        return $this;
    }

    public function setPoll_user_id($poll_user_id)
    {
        $this->poll_user_id = self::makeInt($poll_user_id);
        return $this;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\PollVote
     */
    public static function setFromRow(\Dibi\Row $values)
    {
        $pollVote = new PollVote();
        
        if (isset($values->poll_vote_id)) {
            $pollVote->setPoll_vote_id($values->poll_vote_id);
        }
        
        if (isset($values->poll_id)) {
            $pollVote->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer_id)) {
            $pollVote->setPoll_answer_id($values->poll_answer_id);
        }

        if (isset($values->poll_user_id)) {
            $pollVote->setPoll_user_id($values->poll_user_id);
        }        
                      
        return $pollVote;
    }


        /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\PollVote
     */
    public static function setFromArrayHash(\Nette\Utils\ArrayHash $values)
    {
        $pollVote = new PollVote();
        
        if (isset($values->poll_vote_id)) {
            $pollVote->setPoll_vote_id($values->poll_vote_id);
        }
        
        if (isset($values->poll_id)) {
            $pollVote->setPoll_id($values->poll_id);
        }

        if (isset($values->poll_answer_id)) {
            $pollVote->setPoll_answer_id($values->poll_answer_id);
        }

        if (isset($values->poll_user_id)) {
            $pollVote->setPoll_user_id($values->poll_user_id);
        }        
                      
        return $pollVote;
    }    

    /**
     * 
     * @return []
     */
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
