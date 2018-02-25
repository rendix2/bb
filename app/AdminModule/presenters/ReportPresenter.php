<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\ReportsManager;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 */
class ReportPresenter extends Base\AdminPresenter {

    /**
     * ReportPresenter constructor.
     *
     * @param ReportsManager $manager
     */
    public function __construct(ReportsManager $manager) {
        parent::__construct($manager);
    }

    /**
     *
     */
    public function renderForum() {
        
    }

    /**
     *
     */
    public function renderTopic() {
        
    }

    /**
     *
     */
    public function renderUser() {
        
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addSelect('report_status', 'Report status:', [0 => 'Added', 1 => 'Fixed' ]);
        
        return $this->addSubmitB($form);
    }

}
