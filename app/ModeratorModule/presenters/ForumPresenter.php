<?php

namespace App\ModeratorModule\Presenters;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 */
class ForumPresenter  extends Base\ModeratorPresenter
{
    public function __construct(\App\Models\ForumsManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function renderDefault($page = 1)
    {
        $this->template->forums = $this->moderatorsManager->getAllJoinedByLeft($this->getUser()->getId());
    }

    protected function createComponentEditForm()
    {
        $form = \App\Controls\BootstrapForm::create();
        $form->addTextArea('forum_rules', 'Forum rules:');
        
        return $this->addSubmitB($form);
    }
    
}
