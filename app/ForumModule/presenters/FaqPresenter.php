<?php

namespace App\ForumModule\Presenters;

use App\Models\FaqManager;
use App\Presenters\crud\CrudPresenter;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 * @method FaqManager getManager()
 * @package App\ForumModule\Presenters
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

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

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
