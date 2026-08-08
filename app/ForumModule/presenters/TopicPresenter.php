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
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PollAnswerRepository;
use App\Model\Repository\PollRepository;
use App\Model\Repository\PollVoteRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\RankRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Model\Repository\UserRepository;
use App\Models\Posts2FilesManager;
use App\Models\ThanksFacade;
use App\Models\TopicFacade;
use App\Models\TopicManager;
use App\services\BreadcrumbService;
use App\services\ScopeService;
use App\Settings\Avatars;
use App\Settings\PostSetting;
use App\Settings\TopicsSetting;
use Doctrine\DBAL\Exception;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
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
class TopicPresenter extends Presenter
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
     * @var PostSetting $postSettings
     * @inject
     */
    public PostSetting $postSettings;
    
    /**
     *
     * @var Posts2FilesManager $posts2FilesManager
     * @inject
     */
    public Posts2FilesManager $posts2FilesManager;

    public function __construct(
        TopicManager                            $manager,
        private readonly EntityManagerDecorator $em,
        private readonly TopicFastReplyForm     $topicFastReplyForm,
        private readonly ReportForm             $reportForm,
        private readonly TopicJumpToForumForm   $topicJumpToForumForm,

        private readonly ScopeService      $scopeService,
        private readonly BreadcrumbService $breadcrumbService,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
        private readonly TopicRepository    $topicRepository,
        private readonly PostRepository     $postRepository,
        private readonly UserRepository     $userRepository,

        private readonly PollRepository       $pollRepository,
        private readonly PollAnswerRepository $pollAnswerRepository,
        private readonly PollVoteRepository   $pollVoteRepository,

        private readonly RankRepository       $rankRepository,
        private readonly ThankRepository      $thankRepository,
        private readonly TopicWatchRepository $topicWatchRepository,

        private readonly PollControl $pollControl,
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
    public function actionStartWatch(int $category_id, int $forum_id, int $topic_id, int $page): void
    {
        $categoryEntity = $this->categoryRepository
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

        $user_id = $this->getUser()->getId();

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
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

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
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

        $topicWatchEntity = $this->topicWatchRepository
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
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $user_id  = $this->getUser()->getId();
        
        $forumScope = $this->scopeService->loadForum($forumEntity);
        
        $this->requireAccess($forumScope, ForumScope::ACTION_THANK);

        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $thankEntity = new \App\Model\Entity\ThankEntity();
        $thankEntity->category = $categoryEntity;
        $thankEntity->forum = $forumEntity;
        $thankEntity->topic = $topicEntity;
        $thankEntity->post = null;
        $thankEntity->user = $userEntity;
        $thankEntity->ipAddress = $this->getHttpRequest()->getRemoteAddress();

        try {
            $this->em->persist($thankEntity);
            $this->em->flush();

            $this->flashMessage('Your thank to this topic.', self::FLASH_MESSAGE_SUCCESS);
        } catch (Exception $exception) {
            $this->flashMessage('Thank was not saved');
            $this->redrawControl('flashes');
        }
        
        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id);
    }

    public function actionDelete($category_id, $forum_id, $topic_id, $page): void
    {
        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        if ($forumEntity === null) {
            $this->error('Forum was not found.');
        }

        if ($forumEntity->active === false) {
            $this->error('Forum is not active.');
        }

        if ($topicEntity === null) {
            $this->error('Topic was not found.');
        }
        
        $forumScope = $this->scopeService->loadForum($forumEntity);
        $topicScope = $this->scopeService->loadTopic($forumEntity, $topicEntity);
        
        $this->requireAccess($topicScope, TopicScope::ACTION_DELETE);

        $res = $this->topicFacade->delete($topicEntity);
        
        if ($res) {
            $this->flashMessage('Topic was deleted.', 'success');
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
        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        if ($forumEntity === null) {
            $this->error('Forum was not found.');
        }

        if ($forumEntity->active === false) {
            $this->error('Forum is not active.');
        }

        if ($topicEntity === null) {
            $this->error('Topic was not found.');
        }
        
        $forumScope = $this->scopeService->loadForum($forumEntity);
        $topicScope = $this->scopeService->loadTopic($forumEntity, $topicEntity);

        $posts = $this->em
            ->createQueryBuilder('_p')

            ->addSelect('_u')
            ->innerJoin('_p.user', '_u')

            ->where('_p.topic = :topic')
            ->setParameter('topic', $topicEntity)

            ->getQuery()
            ->getResult();

        if ($this->topicSetting->get()['logViews']) {
            $this->getManager()->update($topic_id, ArrayHash::from(['topic_view_count%sql' => 'topic_view_count + 1']));
        }

        $topicSettings = $this->topicSetting->get();
        
        $pagination = new PaginatorControl(
            $posts,
            $topicSettings['pagination']['itemsPerPage'],
            $topicSettings['pagination']['itemsAroundPagination'],
            $page
        );
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $category_id, $forum_id);
        }

        $postsNew  = [];
        $posts_ids = [];

        foreach ($posts as $post) {
            $postScope = $this->scopeService->loadPost($forumEntity, $topicEntity, $post);
            
            $post->canDelete  = $this->isAllowed($postScope, PostScope::ACTION_DELETE);
            $post->canEdit    = $this->isAllowed($postScope, PostScope::ACTION_EDIT);
            $post->canHistory = $this->isAllowed($postScope, PostScope::ACTION_HISTORY);
           
            $postsNew[]  = $post;
            $posts_ids[] = $post->getPost_id();
        }
        
        $files = $this->posts2FilesManager->getAllByLeftsJoined($posts_ids);
        
        foreach ($postsNew as $post) {
            $post->post_files = [];
            
            foreach ($files as $file) {
                if ($post->post_id === $file->post->id) {
                    $post->post_files[] = $file;
                }
            }
        }
                
        $this->template->posts = $postsNew;
        $this->template->topic = $topicEntity;
        
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

        $ranks = $this->rankRepository->findAll();

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $topicWatchEntity = $this->topicWatchRepository
            ->findOneBy(
                [
                    'topic' => $topicEntity,
                    'user' => $userEntity,
                ]
            );

        $this->getTemplate()->avatarsDir = $this->avatars->getTemplateDir();
        $this->getTemplate()->topicWatch = $topicWatchEntity;
        $this->getTemplate()->ranks      = $ranks;
        
        $this->template->thanks     = $topicEntity->thanks;
        $this->getTemplate()->signatureDelimiter = $this->postSettings->get()['signatureDelimiter'];
    }

    /**
     * @param int      $category_id
     * @param int      $forum_id
     * @param int|null $topic_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id = null): void
    {
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $forumScope = $this->scopeService->loadForum($forumEntity);

        if ($topic_id) {
            $this->requireAccess($forumScope, ForumScope::ACTION_TOPIC_UPDATE);
        } else {
            $this->requireAccess($forumScope, ForumScope::ACTION_TOPIC_ADD);
        }

        $topic = [];
        $post  = [];
        
        if ($topic_id) {
            $topic = $this->topicRepository
                ->findOneBy(
                    [
                        'id' => $topic_id,
                    ]
                );

            $post = $this->postRepository->findFirstByTopicId($topic_id);

            if (!$post) {
                $this->error('Post was not found.');
            }

            $poll = $this->pollRepository->findByTopicId($topic_id);
                        
            if ($poll) {
                $this['editForm']->setDefaults(
                    [
                        'poll_question' => $poll->question,
                        'poll_time_to' => date('d.m.Y', $poll->poll_time_to)
                    ]
                );
                
                $pollAnswers = $this->pollAnswerRepository->findByPollId($poll->id);

                $this['editForm-answers']->setValues($pollAnswers);
            }
            
            $this['editForm']->setDefaults(
                [
                    'post_title' => $topic->name,
                    'post_text' => $post->text
                ]
            );
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
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderWatchers($category_id, $forum_id, $topic_id): void
    {
        $userId = $this->getUser()->getId();

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
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

        $watchers = $this->topicWatchRepository
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

    public function renderThanks(int $category_id, int $forum_id, int $topic_id): void
    {
        $categoryEntity = $this->categoryRepository
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

        $thanks = $this->thankRepository->findByTopicJoinedUser($topic_id);

        if (!$thanks) {
            $this->flashMessage('Topic has not any thanks.', self::FLASH_MESSAGE_INFO);
        }
        
        $this->getTemplate()->thanks = $thanks;
    }

    public function renderFiles(int $category_id, int $forum_id, int $topic_id): void
    {
        $categoryEntity = $this->categoryRepository
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
    
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $user_id     = $this->getUser()->getId();
        $page        = $this->getParameter('page');

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $pollEntity = $this->pollRepository->findByTopicId($topic_id);

        $userEntity = $this->userRepository->findOneByNetteUser($this->getUser());

        $createPoll = function (
            ArrayHash $values,
            CategoryEntity $categoryEntity,
            ForumEntity $forumEntity,
            \App\Model\Entity\TopicEntity $topicEntity,
            UserEntity $userEntity,
        ) : ?\App\Model\Entity\PollEntity
        {
            if ($values->poll_question) {
                $pollEntity = new \App\Model\Entity\PollEntity();
                $pollEntity->category = $categoryEntity;
                $pollEntity->forum = $forumEntity;
                $pollEntity->topic = $topicEntity;
                $pollEntity->user = $userEntity;

                foreach ($values->answers as $answer) {
                    $pollAnswerEntity = new \App\Model\Entity\PollAnswerEntity();
                    $pollAnswerEntity->poll = $pollEntity;
                    //$pollAnswerEntity->text = $answer->poll_answer;
                    $pollAnswerEntity->category = $categoryEntity;
                    $pollAnswerEntity->forum = $forumEntity;
                    $pollAnswerEntity->topic = $topicEntity;
                    $pollAnswerEntity->user = $userEntity;

                    $pollEntity->answers->add($pollAnswerEntity);
                }

                return $pollEntity;
            }

            return null;
        };

        if ($topic_id) {
            $topicEntity = $this->topicRepository
                ->findOneBy(
                    [
                        'id' => $topic_id,
                    ]
                );

            $topicEntity->category = $categoryEntity;
            $topicEntity->forum = $forumEntity;
            $topicEntity->poll = $createPoll($values, $categoryEntity, $forumEntity, $topicEntity, $userEntity);
            $topicEntity->user = $userEntity;
            $topicEntity->name = $values->name;
            
            $this->em->persist($topicEntity);
            $this->em->flush();
        } else {
            $topicEntity = new \App\Model\Entity\TopicEntity();
            $topicEntity->category = $categoryEntity;
            $topicEntity->forum = $forumEntity;
            $topicEntity->user = $userEntity;
            $topicEntity->name = $values->name;
            $topicEntity->poll = $createPoll($values, $categoryEntity, $forumEntity, $topicEntity, $userEntity);

            $postEntity = new \App\Model\Entity\PostEntity();
            $postEntity->category = $categoryEntity;
            $postEntity->forum = $forumEntity;
            $postEntity->topic = $topicEntity;
            $postEntity->text = $values->text;
            $postEntity->addIpAddress = $this->getHttpRequest()->getRemoteAddress();
            
            $this->topicFacade->add($topicEntity, $postEntity);
        }

        $this->flashMessage('Topic was saved.', self::FLASH_MESSAGE_SUCCESS);
        
        $this->redirect(':Forum:Topic:default', $category_id, $forum_id, (string)$topic_id, $page);
    }
    
    protected function createComponentPoll(): PollControl
    {
        return $this->pollControl;
    }

    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

    protected function createComponentBreadCrumbThanks(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

    protected function createComponentBreadCrumbWatchers(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

    protected function createComponentJumpToForum(): TopicJumpToForumForm
    {
        return $this->topicJumpToForumForm;
    }

    protected function createComponentFastReply(): TopicFastReplyForm
    {
        return $this->topicFastReplyForm;
    }

    protected function createComponentReportForm(): ReportForm
    {
        return $this->reportForm;
    }
}
