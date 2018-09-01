<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
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
    
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());
        
        $gf = $this->gf;
        $gf->addFilter('report_id', 'report_id', \App\Controls\GridFilter::INT_EQUAL);
        $gf->addFilter('report_time', 'report_time', \App\Controls\GridFilter::DATE_TIME);
        $gf->addFilter('user_name', 'user_name', \App\Controls\GridFilter::TEXT_LIKE);
        $gf->addFilter('forum_name', 'forum_name', \App\Controls\GridFilter::TEXT_LIKE);        
        $gf->addFilter('topic_name', 'topic_name', \App\Controls\GridFilter::TEXT_LIKE);
        $gf->addFilter('post_title', 'post_title', \App\Controls\GridFilter::TEXT_LIKE);
        $gf->addFilter(null, null, \App\Controls\GridFilter::NOTHING);
        
        
        return $gf;
    }
}
