<?php

namespace App\Models\Entity;

/**
 * Description of Poll
 *
 * @author rendix2
 */
class Poll extends Base\Entity
{
    public $poll_id;
    
    public $poll_topic_id;
    
    public $poll_question;
    
    public $poll_time_to;
    
    /**
     *
     * @var PollAnswer[] $pollAnswers
     */
    public $pollAnswers;


    public function __construct(
        $poll_id,
        $poll_topic_id,
        $poll_question,
        $poll_time_to,
        $pollAnswers = []
    ) {
        $this->poll_id       = $poll_id;
        $this->poll_topic_id = $poll_topic_id;
        $this->poll_question = $poll_question;
        $this->poll_time_to  = $poll_time_to;
        $this->pollAnswers   = $pollAnswers;
        
    }

    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Poll
     */
    public static function setFromRow(\Dibi\Row $values)
    {
        return new Poll(
            $values->poll_id,
            $values->poll_topic_id,
            $values->poll_question,
            $values->poll_time_to
        );
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Poll
     */
    public static function setFromArrayHash(\Nette\Utils\ArrayHash $values)
    {
        return new Poll(
            isset($values->poll_id)       ? $values->poll_id       : null,
            isset($values->poll_topic_id) ? $values->poll_topic_id : null, 
            isset($values->poll_question) ? $values->poll_question : null,
            isset($values->poll_time_to)  ? $values->poll_time_to  : null 
        );
    }
    
    
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
