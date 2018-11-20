<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\ForumsManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumsManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class ForumPresenter extends ModeratorPresenter
{
    /**
     * ForumPresenter constructor.
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * 
     * @param int $page
     */
    public function actionDefault($page = 1)
    {
    }

        /**
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        $this->template->forums = $this->moderatorsManager->getAllByLeftJoined($this->user->id);
    }

    /**
     * @return BootstrapForm|mixed
     */
    protected function createComponentEditForm()
    {
        $form = BootstrapForm::create();
        $form->addTextArea('forum_rules', 'Forum rules:');
        
        return $this->addSubmitB($form);
    }
    
    /**
     *
     * @return null
     */
    protected function createComponentGridFilter()
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
