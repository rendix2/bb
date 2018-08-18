<?php

namespace App\Settings;

/**
 * Description of User
 *
 * @author rendix2
 */
class Users {

    private $user;

    /**
     * Users constructor.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function getUser()
    {
        return $this->user;
    }    
}
