<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Models\FaqManager;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 * @method FaqManager getManager()
 */
class FaqPresenter extends AdminPresenter
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
     * @return mixed
     */
    protected function createComponentEditForm()
    {
        return $form = $this->getBootstrapForm();
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
