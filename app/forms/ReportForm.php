<?php

use App\Models\ReportsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of ReportForm
 *
 * @author rendix2
 */
class ReportForm extends Nette\Application\UI\Control
{
    
    /**
     *
     * @var ReportsManager $reportsManager
     */
    private $reportsManager;
    
    public function __construct(ReportsManager $reportsManager) {
        parent::__construct();
        
        $this->reportsManager = $reportsManager;
    }
    
    public function render()
    {
        $this['reportForm']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentReportForm()
    {
        $form = \App\Controls\BootstrapForm::create();

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
        $user_id          = $this->presenter->getUser()->getId();

        $report = new App\Models\Entity\Report(
            null,
            $user_id,
            $forum_id,
            $topic_id,
            $post_id,
            $reported_user_id,
            $pm_id,
            $values->report_text, 
            time(), 
            0
        );

        $res = $this->reportsManager->add($report->getArrayHash());

        if ($res) {
            $this->presenter->flashMessage('Report was saved.', App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCESS);
        }

        
        $presenter = $this->presenter;
        
        if ($presenter instanceof App\ForumModule\Presenters\PostPresenter) {
            $this->presenter->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
        } elseif ($presenter instanceof App\ForumModule\Presenters\TopicPresenter) {
            $this->presenter->redirect('Forum:default', $category_id, $forum_id, $page);
        } elseif ($presenter instanceof App\ForumModule\Presenters\PmPresenter){
            $this->presenter->redirect('Pm:default');
        } elseif ($presenter instanceof App\ForumModule\Presenters\UserPresenter) {
            $this->presenter->redirect('User:profile', $reported_user_id);
        }
    }
    
}
