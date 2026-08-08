<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Models\ReportManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Nette\Localization\Translator;

/**
 * Description of ReportPresenter
 *
 * @author rendix2
 * @method ReportManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class ReportPresenter extends ModeratorPresenter
{
    public function __construct(
        ReportManager $manager,
        private readonly Translator $translator,
    )
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addTextArea('text', 'Report text:');

        $form->addSubmit('send', 'Send');

        $form->onSuccess[]  = [$this, 'editFormValidate'];
        $form->onValidate[] = [$this, 'editFormSuccess'];
        
        return $form;
    }

    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->translator);
        
        $gf = $this->gf;
        $gf->addFilter('report_id', 'report_id', GridFilter::INT_EQUAL);
        $gf->addFilter('user_name', 'report_user_id', GridFilter::TEXT_LIKE, ['alias' => 'u.user_name']);
        $gf->addFilter('report_time', 'report_time', GridFilter::DATE_TIME);
        $gf->addFilter('reported_user_name', 'user_name', GridFilter::TEXT_LIKE, ['alias' => 'u2.user_name']);
        $gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $gf->addFilter('topic_name', 'topic_name', GridFilter::TEXT_LIKE);
        $gf->addFilter('report_pm_id', 'report_pm_id', GridFilter::TEXT_LIKE);
        $gf->addFilter('post_title', 'post_title', GridFilter::TEXT_LIKE);
        $gf->addFilter(null, null, GridFilter::NOTHING);
        
        return $gf;
    }
}
