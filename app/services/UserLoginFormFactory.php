<?php

namespace App\Services;

use App\Forms\UserLoginForm;

/**
 * Description of UserLoginFormFactory
 *
 * @author rendix2
 */
interface UserLoginFormFactory
{
    /**
     * @return UserLoginForm
     */
    public function create();
}
