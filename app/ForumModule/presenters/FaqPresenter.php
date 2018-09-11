<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\FaqManager;
use App\Presenters\crud\CrudPresenter;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 */
class FaqPresenter extends CrudPresenter
{
    /**
     * FaqPresenter constructor.
     *
     * @param FaqManager $manager
     */
    public function __construct(FaqManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        return $form;
    }
    
    /**
     *
     * @return null
     */
    protected function createComponentGridFilter()
    {
        return null;
    }
}
