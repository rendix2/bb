<?php

namespace App\Controls;

use App\Models\ForumsManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of JumpToForumControl
 *
 * @author rendi
 */
class TopicJumpToForumForm extends Control
{
    /**
     * @var ForumsManager $forumManager
     */
    private $forumManager;

    /**
     * JumpToForumControl constructor.
     *
     * @param ForumsManager $forumManager
     */
    public function __construct(ForumsManager $forumManager)
    {
        parent::__construct();

        $this->forumManager = $forumManager;
    }

    /**
     * render jump to foorum
     */
    public function render()
    {       
        $this['jumpToForum']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentJumpToForum()
    {
        $form = BootstrapForm::create();

        $form->addSelect('forum_id', null, $this->forumManager->getAllPairsCached('forum_name'));
        $form->addSubmit('send', 'Redirect');

        $form->onSuccess[] = [$this, 'jumpToForumSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function jumpToForumSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter
            ->redirect(
                ':Forum:Forum:default',
                $values->forum_id
            );
    }    
}