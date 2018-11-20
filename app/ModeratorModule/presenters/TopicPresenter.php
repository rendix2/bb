<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\TopicFacade;
use App\Models\TopicsManager;
use App\Models\Traits\ForumsTrait;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicsManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class TopicPresenter extends ModeratorPresenter
{
    
    use ForumsTrait;
    
    /**
     *
     * @var TopicFacade $topicFacade
     * @inject
     */
    public $topicFacade;

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
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentMoveTopic()
    {
        $form = BootstrapForm::create();
        
        $form->addSelect('topic_forum_id', 'Forum name:', $this->forumsManager->getAllPairsCached('forum_name'));
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
        $form->addSubmit('send', 'Search');
        
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

    /**
     * @return BootstrapForm
     */
    protected function createComponentMergeTopics()
    {
        $form = BootstrapForm::create();
        
        $form->addSelect('topic_from_id', 'Topic from', $items);
        $form->addSelect('topic_target_id', 'Topic target', $items);
        
        $form->addSubmit('send', 'Merge topic');
        $form->onSuccess[] = [$this, 'mergeTopicSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function mergeTopicSuccess(Form $form, ArrayHash $values)
    {
        $this->topicFacade->mergeTwoTopics($values->topic_from_id, $values->topic_target_id);
        
        $this->flashMessage('Topics was merged.', self::FLASH_MESSAGE_SUCCESS);
    }

    /**
     * @param int $forum_id
     */
    public function renderTopics($forum_id)
    {
        //$forum = $this->checkForumParam($forum_id);
        //$forumScope = $this->loadForum($forum);
        
        //$this->isAllowed($forumScope, \App\Authorization\Scopes\Forum::ACTION_TOPIC_UPDATE);
        
        
        $this->template->topics = $this->getManager()->getAllByForum($forum_id);
    }
}
