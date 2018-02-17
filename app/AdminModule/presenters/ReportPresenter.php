<?php

namespace App\AdminModule\Presenters;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 */
class ReportPresenter extends Base\AdminPresenter {

    public function __construct(\App\Models\ReportsManager $manager) {
        parent::__construct($manager);
    }

    public function renderForum() {
        
    }

    public function renderTopic() {
        
    }

    public function renderUser() {
        
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->addTextAreaHtml('report_text', 'Report text:');
        
        return $this->addSubmitB($form);
    }

}
