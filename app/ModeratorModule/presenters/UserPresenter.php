<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Models\UsersManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of UserPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\ModeratorModule\Presenters
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

    /**
     * @return mixed|void
     */
    protected function createComponentEditForm()
    {
        return null;
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;
    }
}
