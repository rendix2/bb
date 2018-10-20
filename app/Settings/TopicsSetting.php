<?php

namespace App\Settings;

/**
 * Description of TopicsSetting
 *
 * @author rendix2
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
    
    public function get()
    {
        return $this->topic;
    }

    /**
     * @return mixed
     */
    public function canLogView()
    {
        return $this->topic['logViews'];
    }
}
