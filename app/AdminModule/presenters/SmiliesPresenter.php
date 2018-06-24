<?php

namespace App\AdminModule\Presenters;

use App\Models\SmiliesManager;

/**
 * Description of SmiliesPresenter
 *
 * @author rendi
 */
class SmiliesPresenter extends Base\AdminPresenter
{
    public function __construct(SmiliesManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm()
    {
        $form = new $this->getBootstrapForm();
        
        return $form;
    }

}
