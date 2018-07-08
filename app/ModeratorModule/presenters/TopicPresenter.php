<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\TopicsManager;

/**
 * Description of TopicPresenter
 *
 * @author rendi
 */
class TopicPresenter extends \App\ModeratorModule\Presenters\Base\ModeratorPresenter
{
    /**
     * TopicPresenter constructor.
     *
     * @param TopicsManager $manager
     */
    public function __construct(TopicsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * 
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = BootstrapForm::create();
        
        $form->addText('topic_user_id', 'Topic user:');
        $form->addText('topic_forum_id', 'Topic forum');
        $form->addText('topic_name', 'Topic name:');
        $form->addCheckbox('topic_locked', 'Topic locked:');

        return $this->addSubmitB($form);
    }
}
