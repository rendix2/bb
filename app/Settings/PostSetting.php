<?php

namespace App\Settings;

/**
 * Description of PostSetting
 *
 * @author rendix2
 */
class PostSetting
{

    private $post;
    
    public function __construct(array $post)
    {
        $this->post = $post;
    }
    
    public function get()
    {
        return $this->post;
    }
    
}
