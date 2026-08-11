<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use Nette\Application\UI\Presenter;

class UserPresenter extends Presenter
{
    public function __construct()
    {
        parent::__construct();
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
