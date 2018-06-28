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
    /**
     * SmiliesPresenter constructor.
     *
     * @param SmiliesManager $manager
     */
    public function __construct(SmiliesManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return mixed
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        
        return $form;
    }

}
