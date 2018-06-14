<?php

namespace App\ForumModule\Presenters;

/**
 * Description of FaqPresenter
 *
 * @author rendi
 */
class FaqPresenter extends \App\Presenters\crud\CrudPresenter {

    public function __construct(\App\Models\FaqManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        
    }
}
