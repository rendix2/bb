<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
use App\Models\TopicsManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 */
class TopicPresenter extends ModeratorPresenter
{
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;

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

    /**
     * @return BootstrapForm
     */
    protected function createComponentMoveTopic()
    {
        $form = BootstrapForm::create();
        
        $form->addSelect('topic_forum_id', 'Forum name:', $this->forumsManager->getAllPairsCached('forum_nane'));
        $form->onSuccess[] = [$this, 'moveTopicSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function moveTopicSuccess(Form $form, ArrayHash $values)
    {
        $this->getManager()->update($this->getParameter('id'), $values);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentChangeTopicAuthor()
    {
        $form = BootstrapForm::create();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('sned', 'Search');
        
        $form->onSuccess[] = [$this, 'changeTopicAuthorSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeTopicAuthorSuccess(Form $form, ArrayHash $values)
    {
        
    }
}
