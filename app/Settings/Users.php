<?php

namespace App\Settings;

/**
 * Description of User
 *
 * @author rendi
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
