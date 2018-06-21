<?php

namespace App\ModeratorModule;

use App\Models\TopicsManager;

/**
 * Description of TopicPresenter
 *
 * @author rendi
 */
class TopicPresenter extends ModeratorPresenter
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
     */
    protected function createComponentEditForm()
    {
        $form = new \App\Controls\BootstrapForm();
        
        return $form;
    }
}
