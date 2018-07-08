<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\ReportsManager;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 */
class ReportPresenter extends \App\ModeratorModule\Presenters\Base\ModeratorPresenter
{
    /**
     * ReportPresenter constructor.
     *
     * @param ReportsManager $manager
     */
    public function __construct(ReportsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     *
     */
    protected function createComponentEditForm()
    {
        $form = BootstrapForm::create();
        
        $form->addTextArea('report_text', 'Report text:');
        
        return $this->addSubmitB($form);            
    }
}
