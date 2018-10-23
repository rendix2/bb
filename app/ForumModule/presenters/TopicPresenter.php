<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\PollControl;
use App\Controls\PaginatorControl;
use App\Controls\TopicJumpToForumForm;
use App\Forms\TopicFastReplyForm;
use App\Forms\ReportForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Models\PostFacade;
use App\Models\PollsFacade;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\ThanksFacade;
use App\Models\ThanksManager;
use App\Models\TopicFacade;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Settings\Avatars;
use App\Settings\PostSetting;
use App\Settings\TopicsSetting;
use dibi;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Container;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicsManager getManager()
 */
class TopicPresenter extends BaseForumPresenter
{
    use \App\Models\Traits\CategoriesTrait;
    use \App\Models\Traits\ForumsTrait;
    use \App\Models\Traits\TopicsTrait;
    use \App\Models\Traits\PostTrait;

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
     * @var ThanksManager $thanksManager
     * @inject
     */
    public $thanksManager;

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
     * @param TopicsManager $manager
     */
    public function __construct(TopicsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * 
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

        $user_id = $this->getUser()->getId();
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

        $user_id = $this->getUser()->getId();
        $res = $this->topicWatchManager->fullDelete($topic_id, $user_id);

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
        if (!$this->getUser()->isAllowed($forum_id, 'topic_thank')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $user_id  = $this->getUser()->getId();
        
        $thank = new \App\Models\Entity\Thank(
            null,
            $forum_id,
            $topic_id,
            $user_id,
            time(),
            $this->getHttpRequest()->getRemoteAddress()
        );

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
        if (!$this->getUser()->isAllowed($forum_id, 'topic_delete')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        if ($topic->topic_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of topic.', IResponse::S403_FORBIDDEN);
        }

        $res = $this->topicFacade->delete($topic);
        
        if ($res) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Forum:default', $category_id, $forum_id, $page);
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
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        $data = $this->postsManager->getFluentByTopicJoinedUser($topic_id);

        if ($this->topicSetting->get()['logViews']) {
            $this->getManager()->update($topic_id, ArrayHash::from(['topic_view_count%sql' => 'topic_view_count + 1']));
        }

        $topicSettings = $this->topicSetting->get();
        
        $pagination = new PaginatorControl($data, $topicSettings['pagination']['itemsPerPage'], $topicSettings['pagination']['itemsAroundPagination'], $page);
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $category_id, $forum_id);
        }

        $user_id = $this->getUser()->getId();

        $this->template->avatarsDir = $this->avatars->getTemplateDir();
        $this->template->topicWatch = $this->topicWatchManager->fullCheck($topic_id, $user_id);
        $this->template->ranks      = $this->rankManager->getAllCached();
        $this->template->posts      = $data->orderBy('post_id', dibi::ASC)->fetchAll();
        $this->template->canThank   = $this->thanksManager->canUserThank($forum_id, $topic_id, $user_id);
        $this->template->thanks     = $this->thanksManager->getAllJoinedUserByTopic($topic_id);
        $this->template->forum      = $forum;
        $this->template->topic      = $topic;
        
        $this->template->signatureDelimiter = $this->postSettings->get()['signatureDelimiter'];
    }

    /**
     * @param $category_id
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id = null)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);        

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
                $this['editForm']->setDefaults(['poll_question' => $poll->poll_question, 'poll_time_to' => date('d.m.Y', $poll->poll_time_to)]);
                
                $pollAnswers = $this->pollsFacade->getPollsAnswersManager()->getAllByPoll($poll->poll_id);

                $this['editForm-answers']->setValues($pollAnswers);
            }
            
            $this['editForm']->setDefaults(['post_title' => $topic->topic_name, 'post_text' => $post->post_text]);
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

        $watchers = $this->topicWatchManager->getAllJoinedByLeft($topic_id);
        
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

        $thanks = $this->thanksManager->getAllJoinedUserByTopic($topic_id);
        
        if (!$thanks) {
            $this->flashMessage('Topic has not any thanks.', self::FLASH_MESSAGE_INFO);
        }
        
        $this->template->thanks = $thanks;
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
                   ->setValidationScope(FALSE) # disables validation
                   ->addRemoveOnClick();
        }, 1);
        $answers->addSubmit('add', 'Add answer')
                ->setValidationScope(FALSE) # disables validation
                ->addCreateOnClick(true);

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
        $user_id     = $this->getUser()->getId();
        $page        = $this->getParameter('page');
        
        if ($values->poll_question) {        
            $pollAnswers = [];
        
            foreach ($values->answers as $answer) {   
                $pollAnswer = \App\Models\Entity\PollAnswer::setFromArrayHash($answer);
                
                if ($pollAnswer->poll_answer) {
                    $pollAnswers[] = $pollAnswer;
                }                                
            }
        
            $poll = \App\Models\Entity\Poll::setFromArrayHash($values);
            $poll->pollAnswers = $pollAnswers;
        } else {
            $poll = null;
        }

        if ($topic_id) {
            $oldTopicDibi = $this->getManager()->getById($topic_id);
            $oldTopic     = \App\Models\Entity\Topic::get($oldTopicDibi);
            
            $firstPost = $this->postsManager->getFirstByTopic($oldTopicDibi->topic_id);           
            $pollDibi = $this->pollsFacade->getPollsManager()->getByTopic($topic_id);
            
            if ($pollDibi) {
                $poll->poll_id = $pollDibi->poll_id;
            }
            
            foreach ($poll->pollAnswers as $answer) {
                $answer->poll_id = $pollDibi->poll_id;
            }
                                    
            $post = new \App\Models\Entity\Post(
                $firstPost->post_id,
                $firstPost->post_user_id,
                $firstPost->post_category_id,
                $firstPost->post_forum_id,
                $firstPost->post_topic_id,
                $firstPost->post_title, 
                $values->post_text,
                $firstPost->post_add_time, 
                $firstPost->post_add_user_ip, 
                $firstPost->post_edit_user_ip,
                $firstPost->post_edit_count, 
                $firstPost->post_last_edit_time, 
                $firstPost->post_locked,
                $firstPost->post_order
            );
            
            $topic = new \App\Models\Entity\Topic(
                $topic_id,
                $category_id,
                $forum_id,
                $user_id,
                $values->post_title, 
                $oldTopic->topic_post_count,
                $oldTopic->topic_add_time,
                $oldTopic->topic_locked,
                $oldTopic->topic_view_count,
                $oldTopic->topic_first_post_id,
                $oldTopic->topic_first_user_id,
                $oldTopic->topic_last_post_id,
                $oldTopic->topic_last_user_id, 
                $oldTopic->topic_order,
                $oldTopic->topic_page_count,
                $post,
                $poll
            );
            
            $res = $this->topicFacade->update($topic);
        } else {
            $post = new \App\Models\Entity\Post(
                null,
                $user_id,
                $category_id,
                $forum_id,
                $topic_id,
                $values->post_title, 
                $values->post_text,
                time(), 
                $this->getHttpRequest()->getRemoteAddress(),
                '',
                0, 
                0, 
                0,
                1,
                1
            );

            $topic = new \App\Models\Entity\Topic(
                $topic_id,
                $category_id,
                $forum_id, 
                $user_id, 
                $values->post_title, 
                0,
                time(), 
                0, 
                0,
                0,
                $user_id,
                0,
                $user_id,
                1, 
                1,
                $post,
                $poll                    
            );
            
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
        return new PollControl($this->pollsFacade, $this->user, $this->getForumTranslator());
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

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
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

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
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
                [['link'   => 'Topic:default',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ],
                  'text'   => 'menu_topic']],
                [['text' => 'report_topic']]
        );        

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
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
                [['link'   => 'Topic:default',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ],
                  'text'   => 'menu_topic']],
                [['text' => 'Thanks']]
        );        

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
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
                [['link'   => 'Topic:default',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ],
                  'text'   => 'menu_topic']],
                [['text' => 'watches']]
        );         

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return TopicJumpToForumForm
     */
    protected function createComponentJumpToForum()
    {
        return new TopicJumpToForumForm($this->forumsManager);
    }

    /**
     * @return TopicFastReplyForm
     */
    protected function createComponentFastReply()
    {
        return new TopicFastReplyForm($this->translatorFactory, $this->getUser(), $this->postFacade, $this->getHttpRequest());
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentReportForm()
    {
        return new ReportForm($this->reportManager);
    }    
}
