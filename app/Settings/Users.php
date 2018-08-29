<?php

namespace App\Settings;

/**
 * Description of User
 *
 * @author rendix2
 */
class Users
{
    /**
     * @var array $user
     */
    private $user;

    /**
     * Users constructor.
     *
     * @param array $user
     */
    public function __construct(array $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }
}
