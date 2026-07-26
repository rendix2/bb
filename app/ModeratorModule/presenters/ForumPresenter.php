<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Models\ForumManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class ForumPresenter extends ModeratorPresenter
{
    /**
     * ForumPresenter constructor.
     *
     * @param ForumManager $manager
     */
    public function __construct(ForumManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     *
     * @param int $page
     */
    public function actionDefault($page = 1): void
    {
    }

    /**
     * @param int $page
     */
    public function renderDefault($page = 1): void
    {
        $this->template->forums = $this->moderatorsManager->getAllByLeftJoined($this->getUser()->getId());
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->addTextArea('rules', 'Forum rules');

        $form->addSubmit('send', 'Save');

        $form->onSuccess[]  = [$this, 'editFormSuccess'];
        $form->onValidate[] = [$this, 'editFormValidate'];

        return $form;
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        //$this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
    }
}
