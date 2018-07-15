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
     * @var \App\Models\ForumsManager $forumsManager
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
    
    protected function createComponentMoveTopic()
    {
        $form = BootstrapForm::create();
        
        $form->addSelect('topic_forum_id', 'Forum name:', $this->forumsManager->getAllPairsCached('forum_nane'));
        $form->onSuccess[] = [$this, 'moveTopicSuccess'];
        
        return $form;
    }
    
    public function moveTopicSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        $this->getManager()->update($this->getParameter('id'), $values);
    }
    
    protected function createComponentChangeTopicAuthor()
    {
        $form = BootstrapForm::create();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('sned', 'Search');
        
        $form->onSuccess[] = [$this, 'changeTopicAuthorSuccess'];
        
        return $form;    
    }
    
    public function changeTopicAuthorSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        
    }
}
