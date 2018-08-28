<?php

namespace App\Settings;

/**
 * Description of PostSetting
 *
 * @author rendix2
 */
class PostSetting
{
    /**
     * @var array $post
     */
    private $post;

    /**
     * PostSetting constructor.
     *
     * @param array $post
     */
    public function __construct(array $post)
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->post;
    }
}
