<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\PaginatorControl;
use App\Controls\PollControl;
use App\Forms\ReportForm;
use App\Forms\TopicFastReplyForm;
use App\Forms\TopicJumpToForumForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Models\Entity\PollEntity;
use App\Models\Entity\PollAnswerEntity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\ThankEntity;
use App\Models\Entity\TopicEntity;
use App\Models\PollsFacade;
use App\Models\PostFacade;
use App\Models\Posts2FilesManager;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\ThanksFacade;
use App\Models\TopicFacade;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Models\Traits\CategoriesTrait;
use App\Settings\Avatars;
use App\Settings\PostSetting;
use App\Settings\TopicsSetting;
use dibi;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicsManager getManager()
 * @package App\ForumModule\Presenters
 */
class TopicPresenter extends BaseForumPresenter
{
    use CategoriesTrait;
    //use \App\Models\Traits\ForumsTrait;
    //use \App\Models\Traits\TopicsTrait;
    //use \App\Models\Traits\PostTrait;

    /**
     * @var TopicsSetting $topicSetting
     * @inject
     */
    public $topicSetting;
    
    /**
     *
     * @var Avatars $avatar
     * @inject
     */
    public $avatars;
    
    /**
     * @var TopicWatchManager $topicWatchManager
     * @inject
     */
    public $topicWatchManager;

    /**
     * @var RanksManager $rankManager
     * @inject
     */
    public $rankManager;
    
    /**
     * @var TopicFacade $topicFacade
     * @inject
     */
    public $topicFacade;
    
    /**
     *
     * @var ThanksFacade $thanksFacade
     * @inject
     */
    public $thanksFacade;

    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public $postFacade;
    
    /**
     *
     * @var ReportsManager $reportManager
     * @inject
     */
    public $reportManager;
    
    /**
     *
     * @var PostSetting $postSettings
     * @inject
     */
    public $postSettings;

    /**
     * @var IStorage $storage
     * @inject
     */
    public $storage;
    
    /**
     *
     * @var PollsFacade $pollsFacade
     * @inject
     */
    public $pollsFacade;
    
    /**
     *
     * @var Posts2FilesManager $posts2FilesManager
     * @inject
     */
    public $posts2FilesManager;

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
     * TopicPresenter destructor.
     */
    public function __destruct()
    {
        $this->categoriesManager = null;
        $this->forumsManager     = null;
        $this->topicsManager     = null;
        $this->postsManager      = null;
        $this->topicSetting      = null;
        $this->avatars           = null;
        $this->topicWatchManager = null;
        $this->thanksManager     = null;
        $this->rankManager       = null;
        $this->topicFacade       = null;
        $this->thanksFacade      = null;
        $this->postFacade        = null;
        $this->reportManager     = null;
        $this->postSettings      = null;
        $this->storage           = null;
        $this->pollsFacade       = null;
        
        parent::__destruct();
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStartWatch($category_id, $forum_id, $topic_id, $page)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $user_id = $this->user->id;
        $res     = $this->topicWatchManager->addByLeft($topic_id, [$user_id]);

        if ($res) {
            $this->flashMessage('You have start watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStopWatch($category_id, $forum_id, $topic_id, $page)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $user_id  = $this->user->id;
        
        $res = $this->topicWatchManager->delete($topic_id, $user_id);

        if ($res) {
            $this->flashMessage('You have stop watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function actionThank($category_id, $forum_id, $topic_id)
    {
        $category   = $this->checkCategoryParam($category_id);
        $forum      = $this->checkForumParam($forum_id, $category_id);
        $topic      = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $user_id    = $this->user->id;
        
        $forumScope = $this->loadForum($forum);
        
        $this->requireAccess($forumScope, ForumScope::ACTION_THANK);

        $thank = new ThankEntity();
        $thank->setThank_forum_id($forum_id)
              ->setThank_topic_id($topic_id)
              ->setThank_user_id($user_id)
              ->setThank_time(time())
              ->setThank_user_ip($this->getHttpRequest()->getRemoteAddress());

        $res = $this->thanksFacade->add($thank);
        
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
    public function actionDelete($category_id, $forum_id, $topic_id, $page)
    {
        $category   = $this->checkCategoryParam($category_id);
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
    public function actionDefault($category_id, $forum_id, $topic_id, $page = 1)
    {
        $category = $this->checkCategoryParam($category_id);
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
    public function renderDefault($category_id, $forum_id, $topic_id, $page = 1)
    {
        $user_id = $this->user->id;
        
        $this->template->avatarsDir = $this->avatars->getTemplateDir();
        $this->template->topicWatch = $this->topicWatchManager->fullCheck($topic_id, $user_id);
        $this->template->ranks      = $this->rankManager->getAllCached();
        
        //$this->template->thanks     = $this->thanksManager->getAllJoinedUserByTopic($topic_id);
        $this->template->signatureDelimiter = $this->postSettings->get()['signatureDelimiter'];
    }

    /**
     * @param int      $category_id
     * @param int      $forum_id
     * @param int|null $topic_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id = null)
    {
        $category   = $this->checkCategoryParam($category_id);
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
    public function renderReport($category_id, $forum_id, $topic_id, $page)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderWatchers($category_id, $forum_id, $topic_id)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $watchers = $this->topicWatchManager->getAllByLeftJoined($topic_id);
        
        if (!$watchers) {
            $this->flashMessage('No watchers.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->watchers = $watchers;
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderThanks($category_id, $forum_id, $topic_id)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $thanks = $this->thanksManager->getAllByTopicJoinedUser($topic_id);
        
        if (!$thanks) {
            $this->flashMessage('Topic has not any thanks.', self::FLASH_MESSAGE_INFO);
        }
        
        $this->template->thanks = $thanks;
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderFiles($category_id, $forum_id, $topic_id)
    {
        $category = $this->checkCategoryParam($category_id);
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

    /**
     *
     * @return BootstrapForm
     */
    public function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

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
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $user_id     = $this->user->id;
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
    protected function createComponentPoll()
    {
        return new PollControl($this->pollsFacade, $this->user, $this->getTranslator());
    }
    
    /**
     * bread crumbs
     */

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['text' => 'menu_topic']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
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
    protected function createComponentBreadCrumbThanks()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
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
    protected function createComponentBreadCrumbWatchers()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
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
    protected function createComponentJumpToForum()
    {
        return new TopicJumpToForumForm($this->forumsManager, $this->getTranslator());
    }

    /**
     * @return TopicFastReplyForm
     */
    protected function createComponentFastReply()
    {
        return new TopicFastReplyForm(
            $this->translatorFactory,
            $this->user,
            $this->postFacade,
            $this->getHttpRequest()
        );
    }

    /**
     * @return ReportForm
     */
    protected function createComponentReportForm()
    {
        return new ReportForm($this->reportManager);
    }
}
