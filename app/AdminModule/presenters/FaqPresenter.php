<?php

namespace App\AdminModule\Presenters;

/**
 * Description of FaqPresenter
 *
 * @author rendi
 */
class FaqPresenter extends Base\AdminPresenter
{
    
    public function __construct(\App\Models\FaqManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        
    }

}
