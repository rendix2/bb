<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\ReportsManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of ReportPresenter
 *
 * @author rendix2
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
        $form = BootstrapForm::create();
        
        $form->addTextArea('report_text', 'Report text:');
        
        return $this->addSubmitB($form);
    }

    /**
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());
        
        $gf = $this->gf;
        $gf->addFilter('report_id', 'report_id', GridFilter::INT_EQUAL);
        $gf->addFilter('report_time', 'report_time', GridFilter::DATE_TIME);
        $gf->addFilter('user_name', 'user_name', GridFilter::TEXT_LIKE);
        $gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $gf->addFilter('topic_name', 'topic_name', GridFilter::TEXT_LIKE);
        $gf->addFilter('post_title', 'post_title', GridFilter::TEXT_LIKE);
        $gf->addFilter(null, null, GridFilter::NOTHING);
        
        return $gf;
    }
}
