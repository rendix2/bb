<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\ForumModule\Presenters\PmPresenter;
use App\ForumModule\Presenters\PostPresenter;
use App\ForumModule\Presenters\TopicPresenter;
use App\ForumModule\Presenters\UserPresenter;
use App\Models\Entity\ReportEntity;
use App\Models\ReportsManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of ReportForm
 *
 * @author rendix2
 * @package App\Forms
 */
class ReportForm extends Control
{
    
    /**
     *
     * @var ReportsManager $reportsManager
     */
    private $reportsManager;
    
    /**
     *
     * @param ReportsManager $reportsManager
     */
    public function __construct(ReportsManager $reportsManager)
    {
        parent::__construct();
        
        $this->reportsManager = $reportsManager;
    }
    
    /**
     *
     */
    public function __destruct()
    {
        $this->reportsManager = null;
    }

    /**
     *
     */
    public function render()
    {
        $this['reportForm']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentReportForm()
    {
        $form = BootstrapForm::create();

        $form->addTextArea('report_text', 'Report text:');
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'reportFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function reportFormSuccess(Form $form, ArrayHash $values)
    {
        $category_id      = $this->presenter->getParameter('category_id');
        $forum_id         = $this->presenter->getParameter('forum_id');
        $topic_id         = $this->presenter->getParameter('topic_id');
        $post_id          = $this->presenter->getParameter('post_id');
        $reported_user_id = $this->presenter->getParameter('user_id');
        $page             = $this->presenter->getParameter('page');
        $pm_id            = $this->presenter->getParameter('pm_iid');
        $user_id          = $this->presenter->user->id;

        $report = new ReportEntity();
        $report->setReport_forum_id($forum_id)
               ->setReport_topic_id($topic_id)
               ->setReport_post_id($post_id)
               ->setReport_reported_user_id($reported_user_id)
               ->setReport_user_id($user_id)
               ->setReport_pm_id($pm_id)
               ->setReport_text($values->report_text)
               ->setReport_time(time())
               ->setReport_status(0);

        $res = $this->reportsManager->add($report->getArrayHash());

        if ($res) {
            $this->presenter->flashMessage('Report was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        }
        
        $presenter = $this->presenter;
        
        if ($presenter instanceof PostPresenter) {
            $this->presenter->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
        } elseif ($presenter instanceof TopicPresenter) {
            $this->presenter->redirect('Forum:default', $category_id, $forum_id, $page);
        } elseif ($presenter instanceof PmPresenter) {
            $this->presenter->redirect('Pm:default');
        } elseif ($presenter instanceof UserPresenter) {
            $this->presenter->redirect('User:profile', $reported_user_id);
        }
    }
}
