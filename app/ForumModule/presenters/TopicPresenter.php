<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Controls\BreadCrumbControl;
use App\Controls\PaginatorControl;
use App\Controls\PollControl;
use App\Database\EntityManagerDecorator;
use App\Forms\ReportForm;
use App\Forms\TopicFastReplyForm;
use App\Forms\TopicJumpToForumForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\RankEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Models\CategoryManager;
use App\Models\Entity\PollEntity;
use App\Models\Entity\PollAnswerEntity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\ThankEntity;
use App\Models\Entity\TopicEntity;
use App\Models\PollsFacade;
use App\Models\PostFacade;
use App\Models\Posts2FilesManager;
use App\Models\ReportManager;
use App\Models\ThanksFacade;
use App\Models\TopicFacade;
use App\Models\TopicManager;
use App\Settings\Avatars;
use App\Settings\PostSetting;
use App\Settings\TopicsSetting;
use dibi;
use Doctrine\DBAL\Exception;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicManager getManager()
 * @package App\ForumModule\Presenters
 */
class TopicPresenter extends BaseForumPresenter
{
    /**
     * @var TopicsSetting $topicSetting
     * @inject
     */
    public TopicsSetting $topicSetting;
    
    /**
     *
     * @var Avatars $avatar
     * @inject
     */
    public Avatars $avatars;

    
    /**
     * @var TopicFacade $topicFacade
     * @inject
     */
    public TopicFacade $topicFacade;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     * @inject
     */
    public ThanksFacade $thanksFacade;

    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public PostFacade $postFacade;
    
    /**
     *
     * @var ReportManager $reportsManager
     * @inject
     */
    public ReportManager $reportsManager;
    
    /**
     *
     * @var PostSetting $postSettings
     * @inject
     */
    public PostSetting $postSettings;

    /**
     * @var IStorage $storage
     * @inject
     */
    public IStorage $storage;
    
    /**
     *
     * @var PollsFacade $pollsFacade
     * @inject
     */
    public PollsFacade $pollsFacade;
    
    /**
     *
     * @var Posts2FilesManager $posts2FilesManager
     * @inject
     */
    public Posts2FilesManager $posts2FilesManager;

    #[Inject]
    public CategoryManager $categoryManager;

    /**
     * TopicPresenter constructor.
     *
     * @param TopicManager $manager
     */
    public function __construct(
        TopicManager                            $manager,
        private readonly EntityManagerDecorator $em,
        private readonly TopicFastReplyForm     $topicFastReplyForm,
        private readonly ReportForm             $reportForm,
        private readonly TopicJumpToForumForm   $topicJumpToForumForm,
    )
    {
        parent::__construct($manager);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStartWatch($category_id, $forum_id, $topic_id, $page): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $user_id = $this->getUser()->getId();

        $topicEntity = $this->em
            ->getRepository(TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $topicWatchEntity = new TopicWatchEntity();
        $topicWatchEntity->topic = $topicEntity;
        $topicWatchEntity->user= $userEntity;

        try {
            $this->em->persist($topicWatchEntity);
            $this->em->flush();

            $this->flashMessage('You have start watching topic.', self::FLASH_MESSAGE_SUCCESS);
        } catch (Exception $exception) {
            $this->flashMessage('Error during adding to watched topics', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStopWatch($category_id, $forum_id, $topic_id, $page): void
    {
        $user_id = $this->getUser()->getId();

        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $topicEntity = $this->em
            ->getRepository(\App\Model\Entity\TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $user_id  = $this->getUser()->getId();

        $topicWatchEntity = $this->em
            ->getRepository(TopicWatchEntity::class)
            ->findOneBy(
                [
                    'topic' => $topicEntity,
                    'user' => $userEntity,
                ]
            );

        try {
            $this->em->remove($topicWatchEntity);
            $this->em->flush();

            $this->flashMessage('You have stop watching topic.', 'success');
        } catch (Exception $exception) {
            $this->flashMessage('Error during stopping watching topic', 'danger');
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function actionThank($category_id, $forum_id, $topic_id): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum      = $this->checkForumParam($forum_id, $category_id);
        $topic      = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $user_id  = $this->getUser()->getId();
        
        $forumScope = $this->loadForum($forum);
        
        $this->requireAccess($forumScope, ForumScope::ACTION_THANK);

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $thankEntity = new \App\Model\Entity\ThankEntity();
        $thankEntity->category = $categoryEntity;
        $thankEntity->forum = $forum;
        $thankEntity->topic = $topic;
        $thankEntity->post = null;
        $thankEntity->user = $userEntity;
        $thankEntity->ipAddress = $this->getHttpRequest()->getRemoteAddress();

        $res = $this->thanksFacade->add($thankEntity);
        
        if ($res) {
            $this->flashMessage('Your thank to this topic.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionDelete($category_id, $forum_id, $topic_id, $page): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum      = $this->checkForumParam($forum_id, $category_id);
        $topic      = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        
        $pollDibi   = $this->pollsFacade->getPollsManager()->getByTopic($topic_id);
        
        if ($pollDibi) {
            $pollTimeStamp = $pollDibi->poll_time_to;
            unset($pollDibi->poll_time_to);
        
            $pollEntity = PollEntity::setFromRow($pollDibi);
            $pollEntity->setPoll_time_to(DateTime::from($pollTimeStamp));
        
            $topic->setPoll($pollEntity);
        }
        
        $forumScope = $this->loadForum($forum);
        $topicScope = $this->loadTopic($forum, $topic);
        
        $this->requireAccess($topicScope, TopicScope::ACTION_DELETE);

        $res = $this->topicFacade->delete($topic);
        
        if ($res) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Forum:default', $category_id, $forum_id, $page);
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionDefault($category_id, $forum_id, $topic_id, $page = 1): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        
        $forumScope = $this->loadForum($forum);
        $topicScope = $this->loadTopic($forum, $topic);

        $data = $this->postsManager->getFluentByTopicJoinedUser($topic_id);

        if ($this->topicSetting->get()['logViews']) {
            $this->getManager()->update($topic_id, ArrayHash::from(['topic_view_count%sql' => 'topic_view_count + 1']));
        }

        $topicSettings = $this->topicSetting->get();
        
        $pagination = new PaginatorControl(
            $data,
            $topicSettings['pagination']['itemsPerPage'],
            $topicSettings['pagination']['itemsAroundPagination'],
            $page
        );
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $category_id, $forum_id);
        }

        $posts     = $data->orderBy('post_id', dibi::ASC)->fetchAll();
        $postsNew  = [];
        $postScope = null;
        $posts_ids = [];

        foreach ($posts as $postDibi) {
            $post      = PostEntity::setFromRow($postDibi);
            $postScope = new PostScope($post, $topicScope, $topic);
            
            $postDibi->canDelete  = $this->isAllowed($postScope, PostScope::ACTION_DELETE);
            $postDibi->canEdit    = $this->isAllowed($postScope, PostScope::ACTION_EDIT);
            $postDibi->canHistory = $this->isAllowed($postScope, PostScope::ACTION_HISTORY);
           
            $postsNew[]  = $postDibi;
            $posts_ids[] = $post->getPost_id();
        }
        
        $files = $this->posts2FilesManager->getAllByLeftsJoined($posts_ids);
        
        foreach ($postsNew as $post) {
            $post->post_files = [];
            
            foreach ($files as $file) {
                if ($post->post_id === $file->post_id) {
                    $post->post_files[] = $file;
                }
            }
        }
                
        $this->template->posts = $postsNew;
        $this->template->topic = $topic;
        
        $this->template->canAddPost    = $this->isAllowed($forumScope, ForumScope::ACTION_POST_ADD);
        $this->template->canDeletePost = $this->isAllowed($forumScope, ForumScope::ACTION_POST_DELETE);
        $this->template->canFastReply  = $this->isAllowed($forumScope, ForumScope::ACTION_FAST_REPLY);
        $this->template->canThankTopic = $this->isAllowed($topicScope, TopicScope::ACTION_THANK);
    }

    /**
     * renders posts in topic
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderDefault($category_id, $forum_id, $topic_id, $page = 1): void
    {
        $user_id = $this->getUser()->getId();

        $ranks = $this->em
            ->getRepository(RankEntity::class)
            ->findAll();

        $topicEntity = $this->em
            ->getRepository(\App\Model\Entity\TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $topicWatchEntity = $this->em
            ->getRepository(TopicWatchEntity::class)
            ->findOneBy(
                [
                    'topic' => $topicEntity,
                    'user' => $userEntity,
                ]
            );

        $this->getTemplate()->avatarsDir = $this->avatars->getTemplateDir();
        $this->getTemplate()->topicWatch = $topicWatchEntity;
        $this->getTemplate()->ranks      = $ranks;
        
        //$this->template->thanks     = $this->thanksManager->getAllJoinedUserByTopic($topic_id);
        $this->getTemplate()->signatureDelimiter = $this->postSettings->get()['signatureDelimiter'];
    }

    /**
     * @param int      $category_id
     * @param int      $forum_id
     * @param int|null $topic_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id = null): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum      = $this->checkForumParam($forum_id, $category_id);
        $forumScope = $this->loadForum($forum);

        if ($topic_id) {
            $this->requireAccess($forumScope, ForumScope::ACTION_TOPIC_UPDATE);
        } else {
            $this->requireAccess($forumScope, ForumScope::ACTION_TOPIC_ADD);
        }

        $topic = [];
        $post  = [];
        
        if ($topic_id) {
            $topic = $this->checkTopicParam($topic_id, $category_id, $forum_id);

            $post = $this->postsManager->getFirstByTopic($topic_id);

            if (!$post) {
                $this->error('Post was not found.');
            }

            $poll = $this->pollsFacade->getPollsManager()->getByTopic($topic_id);
                        
            if ($poll) {
                $this['editForm']->setDefaults(
                    [
                        'poll_question' => $poll->poll_question,
                        'poll_time_to' => date('d.m.Y', $poll->poll_time_to)
                    ]
                );
                
                $pollAnswers = $this->pollsFacade->getPollsAnswersManager()->getAllByPoll($poll->poll_id);

                $this['editForm-answers']->setValues($pollAnswers);
            }
            
            $this['editForm']->setDefaults(['post_title' => $topic->getTopic_name(), 'post_text' => $post->post_text]);
        }
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderReport($category_id, $forum_id, $topic_id, $page): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderWatchers($category_id, $forum_id, $topic_id): void
    {
        $userId = $this->getUser()->getId();

        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $topicEntity = $this->em
            ->getRepository(\App\Model\Entity\TopicEntity::class)
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $userId,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $watchers = $this->em
            ->getRepository(TopicWatchEntity::class)
            ->createQueryBuilder('_tw')

            ->addSelect('_user')
            ->leftJoin('_tw.user', '_user')

            ->where('_tw.topic = :topic')
            ->setParameter('topic', $topicEntity)

            ->getQuery()
            ->getResult();
        
        if ($watchers === []) {
            $this->flashMessage('No watchers.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->getTemplate()->watchers = $watchers;
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderThanks($category_id, $forum_id, $topic_id): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $thanks = $this->thanksManager->getAllByTopicJoinedUser($topic_id);
        
        if (!$thanks) {
            $this->flashMessage('Topic has not any thanks.', self::FLASH_MESSAGE_INFO);
        }
        
        $this->getTemplate()->thanks = $thanks;
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderFiles($category_id, $forum_id, $topic_id): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
    }
    
    public static function bbCodeParse($text)
    {
        //$text = 'awdwad [head]awdwad[/head]';
                
        $bbCode = new \App\Services\BBCode();
        $bbCode->addElement('h1', ['open_tag' => '<h1>', 'close_tag' => '</h1>', 'type' => BBCODE_TYPE_NOARG]);
        $bbCode->addElement('h2', ['open_tag' => '<h2>', 'close_tag' => '</h2>', 'type' => BBCODE_TYPE_NOARG]);
        $bbCode->addElement('h3', ['open_tag' => '<h3>', 'close_tag' => '</h3>', 'type' => BBCODE_TYPE_NOARG]);
        $bbCode->addElement('hide', ['open_tag' => '<span style="display:none">', 'close_tag' => '</span>', 'type' => BBCODE_TYPE_NOARG]);
        bdump($bbCode->parse($text));
    }


    public function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        // form
        $form->addGroup('Topic');
        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextAreaHtml('post_text', 'Text', 0, 15)->setRequired(true);
        
        $form->addSubmit('send', 'Send');
        
        $form->addGroup('Poll');
        // poll
        
        $form->addText('poll_question', 'Question');
        $form->addTbDatePicker('poll_time_to', 'Finish');
        
        $answers = $form->addDynamic('answers', function (Container $answer) {
            $answer->addHidden('poll_answer_id');
            $answer->addText('poll_answer', 'Answer');
            $answer->addSubmit('remove', 'Remove answer')
                   ->setValidationScope(false) # disables validation
                   ->addRemoveOnClick();
        }, 1);
        $answers->addSubmit('add', 'Add answer')
                ->setValidationScope(false) # disables validation
                ->addCreateOnClick(true);
        
        $form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        $form->onSuccess[] = [$this,'editFormSuccess'];
        
        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $user_id     = $this->getUser()->getId();
        $page        = $this->getParameter('page');
        
        if ($values->poll_question) {
            $pollAnswers = [];
        
            foreach ($values->answers as $answer) {
                $pollAnswer = PollAnswerEntity::setFromArrayHash($answer);
                
                if ($pollAnswer->getPoll_answer()) {
                    $pollAnswers[] = $pollAnswer;
                }
            }
        
            $poll = PollEntity::setFromArrayHash($values);
            $poll->setPollAnswers($pollAnswers);
        } else {
            $poll = null;
        }

        if ($topic_id) {
            $oldTopicDibi = $this->getManager()->getById($topic_id);
            $oldTopic     = TopicEntity::setFromRow($oldTopicDibi);
            
            $firstPost = $this->postsManager->getFirstByTopic($oldTopicDibi->topic_id);
            $pollDibi  = $this->pollsFacade->getPollsManager()->getByTopic($topic_id);
            
            if ($pollDibi) {
                $poll->setPoll_id($pollDibi->poll_id);
            }
            
            if ($poll) {
                foreach ($poll->getPollAnswers() as $answer) {
                    $answer->setPoll_id($pollDibi->poll_id);
                }
            }
            
            $post = PostEntity::setFromRow($firstPost);
            $post->setPost_text($values->post_text);
            
            $topic = TopicEntity::setFromRow($oldTopicDibi);
            $topic->setTopic_id($topic_id)
                  ->setTopic_category_id($category_id)
                  ->setTopic_forum_id($forum_id)
                  ->setTopic_user_id($user_id)
                  ->setTopic_name($values->post_title)
                  ->setPost($post)
                  ->setPoll($poll);
            
            $res = $this->topicFacade->update($topic);
        } else {
            $post = new PostEntity();
            $post->setPost_user_id($user_id)
                 ->setPost_category_id($category_id)
                 ->setPost_forum_id($forum_id)
                 ->setPost_topic_id($topic_id)
                 ->setPost_title($values->post_title)
                 ->setPost_text($values->post_text)
                 ->setPost_add_time(time())
                 ->setPost_add_user_ip($this->getHttpRequest()->getRemoteAddress())
                 ->setPost_order(1);
            
            $topic = new TopicEntity();
            
            $topic->setTopic_category_id($category_id)
                  ->setTopic_forum_id($forum_id)
                  ->setTopic_user_id($user_id)
                  ->setTopic_name($values->post_title)
                  ->setTopic_add_time(time())
                  ->setTopic_first_user_id($user_id)
                  ->setTopic_last_user_id($user_id)
                  ->setTopic_page_count(1)
                  ->setPoll($poll)
                  ->setPost($post);
            
            $res = $topic_id = $this->topicFacade->add($topic);
        }

        // refresh cache on index page to show this last topic
        $cache = new Cache($this->storage, IndexPresenter::CACHE_NAMESPACE);
        $cache->remove(IndexPresenter::CACHE_KEY_LAST_TOPIC);
        $cache->remove(IndexPresenter::CACHE_KEY_TOTAL_TOPICS);

        if ($res) {
            $this->flashMessage('Topic was saved.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect(':Forum:Topic:default', $category_id, $forum_id, (string)$topic_id, $page);
    }
    
    /**
     *
     * @return PollControl
     */
    protected function createComponentPoll(): PollControl
    {
        return new PollControl($this->pollsFacade, $this->user, $this->getTranslator());
    }
    
    /**
     * bread crumbs
     */

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ],
                'text' => 'menu_topic']],
            [['text' => 'report_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbThanks(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ],
                'text' => 'menu_topic']],
            [['text' => 'Thanks']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbWatchers(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ],
                'text' => 'menu_topic']],
            [['text' => 'watches']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return TopicJumpToForumForm
     */
    protected function createComponentJumpToForum(): TopicJumpToForumForm
    {
        return $this->topicJumpToForumForm;
    }

    /**
     * @return TopicFastReplyForm
     */
    protected function createComponentFastReply(): TopicFastReplyForm
    {
        return $this->topicFastReplyForm;
    }

    /**
     * @return ReportForm
     */
    protected function createComponentReportForm(): ReportForm
    {
        return $this->reportForm;
    }
}
