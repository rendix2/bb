<?php

namespace App\ForumModule\Presenters;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends \App\Presenters\crud\CrudPresenter
{
    public function __construct(\App\Models\PMManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        $form = new \App\Controls\BootstrapForm();
        
        return $form;
    }

}
