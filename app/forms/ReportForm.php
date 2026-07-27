<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\PmPresenter;
use App\ForumModule\Presenters\PostPresenter;
use App\ForumModule\Presenters\TopicPresenter;
use App\ForumModule\Presenters\UserPresenter;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Models\ReportManager;
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
     * ReportForm constructor.
     *
     * @param ReportManager $reportsManager
     */
    public function __construct(
        ReportManager $reportsManager,
        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct();
    }

    /**
     * ReportForm render.
     */
    public function render(): void
    {
        $this['reportForm']->render();
    }

    protected function createComponentReportForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addTextArea('report_text', 'Report text:');
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'reportFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function reportFormSuccess(Form $form, ArrayHash $values): void
    {
        $category_id      = $this->presenter->getParameter('category_id');
        $forum_id         = $this->presenter->getParameter('forum_id');
        $topic_id         = $this->presenter->getParameter('topic_id');
        $post_id          = $this->presenter->getParameter('post_id');
        $reported_user_id = $this->presenter->getParameter('user_id');
        $pm_id            = $this->presenter->getParameter('pm_id');

        $page             = $this->presenter->getParameter('page');

        $user_id          = $this->presenter->getUser()->getId();

        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        $forumEntity = $this->em
            ->getRepository(ForumEntity::class)
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->em
            ->getRepository(TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $postEntity = $this->em
            ->getRepository(PostEntity::class)
            ->findOneBy(
                [
                    'id' => $post_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $reportEntity = new \App\Model\Entity\ReportEntity();
        $reportEntity->category = $categoryEntity;
        $reportEntity->forum = $forumEntity;
        $reportEntity->topic = $topicEntity;
        $reportEntity->post = $postEntity;
        $reportEntity->user = $userEntity;
        $reportEntity->reportText = $values->report_text;
        $reportEntity->status = 0;

        $this->em->persist($reportEntity);
        $this->em->flush($reportEntity);

        //$res = $this->reportsManager->add($report->getArrayHash());

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
