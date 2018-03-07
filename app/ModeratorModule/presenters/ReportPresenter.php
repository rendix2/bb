<?php

namespace App\ModeratorModule;

use App\Models\ReportsManager;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 */
class ReportPresenter extends ModeratorPresenter
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
    }
}
