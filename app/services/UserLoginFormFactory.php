<?php

namespace App\Services;

use App\Forms\UserLoginForm;

/**
 * Description of UserLoginFormFactory
 *
 * @author rendix2
 * @package App\Services
 */
interface UserLoginFormFactory
{
    /**
     * @return UserLoginForm
     */
    public function create();
}
