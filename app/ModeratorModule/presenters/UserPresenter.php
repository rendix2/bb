<?php

namespace App\ModeratorModule\Presenters;

use App\Models\UsersManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of UserPresenter
 *
 * @author rendi
 */
class UserPresenter extends ModeratorPresenter
{
    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

        //put your code here

    /**
     * @return mixed|void
     */
    protected function createComponentEditForm()
    {
    }
}
