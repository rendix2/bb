<?php

namespace App\AdminModule\Presenters;

/**
 * Description of RankPresenter
 *
 * @author rendi
 */
class RankPresenter extends Base\AdminPresenter {

    public function __construct(\App\Models\RanksManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        return $this->addSubmitB($form);
    }

}
