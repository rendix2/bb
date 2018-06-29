<?php

namespace App\Controls;

/**
 * Description of TopicsSetting
 *
 * @author rendi
 */
class TopicsSetting
{
    /**
     * @var array $topic
     */
    private $topic;

    /**
     * TopicsSetting constructor.
     *
     * @param array $topic
     */
    public function __construct(array $topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return mixed
     */
    public function canLogView()
    {
        return $this->topic['logViews'];
        
    }
}
