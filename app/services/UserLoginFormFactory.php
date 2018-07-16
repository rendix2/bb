<?php

namespace App\Services;

use App\Forms\UserLoginForm;

/**
 * Description of UserLoginFormFactory
 *
 * @author rendi
 */
interface UserLoginFormFactory
{
    /**
     * @return UserLoginForm
     */
    public function create();
}
