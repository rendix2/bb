<?php

/**
 * Description of ReportPresenter
 *
 * @author rendi
 */
class ReportPresenter extends ModeratorPresenter {
    
    public function __construct(App\Models\ReportsManager $manager) {
        parent::__construct($manager);
    }

        protected function createComponentEditForm() {
        
    }

}
