<?php

namespace App\AdminModule\Presenters;

use App\Models\FaqManager;
use App\Controls\BreadCrumbControl;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 */
class FaqPresenter extends Base\AdminPresenter
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
     * @return mixed|void
     */
    protected function createComponentEditForm()
    {
        return $form = $this->getBootstrapForm();
    }
}
