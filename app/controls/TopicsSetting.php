<?php

namespace App\Controls;

/**
 * Description of TopicsSetting
 *
 * @author rendi
 */
class TopicsSetting {

    private $topic;
    
    public function __construct(array $topic)
    {
        $this->topic = $topic;
    }
    
    public function canLogView()
    {
        return $this->topic['logViews'];
        
    }
}
