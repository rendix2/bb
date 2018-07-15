<?php

namespace App\ModeratorModule\Presenters;

/**
 * Description of UserPresenter
 *
 * @author rendi
 */
class UserPresenter extends \App\ModeratorModule\Presenters\Base\ModeratorPresenter
{
    public function __construct(\App\Models\UsersManager $manager)
    {
        parent::__construct($manager);
    }

        //put your code here
    protected function createComponentEditForm() {
        
    }

}
