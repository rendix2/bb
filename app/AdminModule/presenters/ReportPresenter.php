<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Models\ReportsManager;

/**
 * Description of ReportPresenter
 *
 * @author rendix2
 * @method ReportsManager getManager()
 * @package App\AdminModule\Presenters
 */
class ReportPresenter extends AdminPresenter
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
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $values = [
            0 => 'Added',
            1 => 'Fixed'
        ];
        
        $form = $this->getBootstrapForm();
        
        $form->addSelect('report_status', 'Report status:', $values);

        return $this->addSubmitB($form);
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('report_id', 'report_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('report_time', 'report_time', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('user_name', 'reporter_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('forum_name', 'report_forum', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('topic_name', 'report_topic', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('post_title', 'report_post', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('reported_user_name', 'reported_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(
            'report_status',
            'report_status',
            GridFilter::CHECKBOX_LIST,
            [
                1 => 'report_solved',
                0 => 'report_added'
            ]
        );
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_reports']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',  'text' => 'menu_index'],
            1 => ['link' => 'Report:default', 'text' => 'menu_reports'],
            2 => ['link' => 'Report:edit',    'text' => 'menu_report'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
