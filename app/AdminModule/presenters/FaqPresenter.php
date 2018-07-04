<?php

namespace App\AdminModule\Presenters;

use App\Models\FaqManager;

/**
 * Description of FaqPresenter
 *
 * @author rendi
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
