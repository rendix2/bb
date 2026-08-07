<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\TopicRepository;
use App\Models\TopicFacade;
use App\Models\TopicManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class TopicPresenter extends ModeratorPresenter
{
    
    /**
     *
     * @var TopicFacade $topicFacade
     * @inject
     */
    public TopicFacade $topicFacade;

    public function __construct(
        TopicManager $manager,
        private readonly EntityManagerDecorator $em,

        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
    )
    {
        parent::__construct($manager);
    }

    public function renderTopics(int $forum_id): void
    {
        //$forum = $this->checkForumParam($forum_id);
        //$forumScope = $this->loadForum($forum);

        //$this->isAllowed($forumScope, \App\Authorization\Scopes\Forum::ACTION_TOPIC_UPDATE);

        $topics = $this->topicRepository
            ->findBy(
                [
                    'forum' => $forum_id,
                ]
            );

        $this->getTemplate()->topics = $topics;
    }
    
    protected function createComponentEditForm(): BootstrapForm
    {
        $form = new BootstrapForm();
        
        $form->addText('user_id', 'User');
        $form->addText('forum_id', 'Forum');
        $form->addText('name', 'Name');
        $form->addCheckbox('topic_locked', 'Topic locked:');

        $form->addSubmit('send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

        return $form;
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        return $this->gf;
    }

    protected function createComponentMoveTopic(): BootstrapForm
    {
        $form = new BootstrapForm();
        
        $form->addSelect('forum_id', 'Forum name:', $this->forumRepository->findPairs());

        $form->addSubmit('send', 'Save');

        $form->onSuccess[] = [$this, 'moveTopicSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function moveTopicSuccess(Form $form, ArrayHash $values): void
    {
        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $this->getParameter('id'),
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $values->forum_id,
                ]
            );

        $topicEntity->forum = $forumEntity;

        $this->em->persist($topicEntity);
        $this->em->flush();

    }

    protected function createComponentChangeTopicAuthor(): BootstrapForm
    {
        $form = new BootstrapForm();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('send', 'Search');
        
        $form->onSuccess[] = [$this, 'changeTopicAuthorSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeTopicAuthorSuccess(Form $form, ArrayHash $values): void
    {
    }

    protected function createComponentMergeTopics(): BootstrapForm
    {
        $form = new BootstrapForm();
        
        $form->addSelect('from_id', 'Topic from', $items);
        $form->addSelect('target_id', 'Topic target', $items);
        
        $form->addSubmit('send', 'Merge topic');

        $form->onSuccess[] = [$this, 'mergeTopicSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function mergeTopicSuccess(Form $form, ArrayHash $values): void
    {
        $this->topicFacade->mergeTwoTopics($values->from_id, $values->target_id);
        
        $this->flashMessage('Topics was merged.', self::FLASH_MESSAGE_SUCCESS);
    }
}
