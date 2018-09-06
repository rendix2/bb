<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumsManager getManager()
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
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        $this->template->forums = $this->moderatorsManager->getAllJoinedByLeft($this->getUser()->getId());
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
}
