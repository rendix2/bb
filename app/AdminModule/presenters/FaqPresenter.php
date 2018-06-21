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
    public function __construct(FaqManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm()
    {
    }
}
