<?php

namespace App\AdminModule\Presenters;

/**
 * Description of SmiliesPresenter
 *
 * @author rendi
 */
class SmiliesPresenter extends \App\Presenters\crud\CrudPresenter
{
    public function __construct(\App\Models\SmiliesManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm()
    {
        $form = new \App\Controls\BootstrapForm();
        
        return $form;
    }

}
