<?php

namespace App\Settings;

/**
 * Description of PostSetting
 *
 * @author rendi
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
