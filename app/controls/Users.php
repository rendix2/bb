<?php

namespace App\Controls;

/**
 * Description of User
 *
 * @author rendi
 */
class Users {

    private $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function getUser()
    {
        return $this->user;
    }    
}
