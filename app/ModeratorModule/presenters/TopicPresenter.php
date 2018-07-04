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
     */
    protected function createComponentEditForm()
    {
        $form = new BootstrapForm();
        
        return $form;
    }
}
