<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\PaginatorControl;
use App\Controls\TopicJumpToForumForm;
use App\Forms\TopicFastReplyForm;
use App\Models\ForumsManager;
use App\Models\PostFacade;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ThanksFacade;
use App\Models\ThanksManager;
use App\Models\TopicFacade;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Settings\Avatars;
use App\Settings\TopicsSetting;
use dibi;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicPresenter
 *
 * @author rendix2
 * @method TopicsManager getManager()
 */
class TopicPresenter extends Base\ForumPresenter
{
    
    /**
     * @var ForumsManager $forumManager
     * @inject
     */
    public $forumsManager;
    
    /**
     *
     * @var PostsManager $manager
     * @inject
     */
    public $postsManager;
    
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
     * @param TopicsManager $manager
     */
    public function __construct(TopicsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStartWatch($forum_id, $topic_id, $page)
    {
        $user_id = $this->getUser()->getId();
        $res     = $this->topicWatchManager->addByLeft($topic_id, [$user_id]);

        if ($res) {
            $this->flashMessage('You have start watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Topic:default', $forum_id, $topic_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStopWatch($forum_id, $topic_id, $page)
    {
        $user_id = $this->getUser()->getId();
        $res = $this->topicWatchManager->fullDelete($topic_id, $user_id);

        if ($res) {
            $this->flashMessage('You have stop watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Topic:default', $forum_id, $topic_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     */
    public function actionThank($forum_id, $topic_id)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'topic_thank')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $user_id = $this->getUser()->getId();

        $data = [
            'thank_forum_id' => $forum_id,
            'thank_topic_id' => $topic_id,
            'thank_user_id'  => $user_id,
            'thank_time'     => time()
        ];

        $res = $this->thanksFacade->add(ArrayHash::from($data));
        
        if ($res) {
            $this->flashMessage('Your thank to this topic.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Topic:default', $forum_id, $topic_id);
    }
    
    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionDelete($forum_id, $topic_id, $page)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'topic_delete')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $res = $this->topicFacade->delete($topic_id);
        
        if ($res) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Forum:default', $forum_id, $page);
    }

    /**
     * renders posts in topic
     *
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderDefault($forum_id, $topic_id, $page = 1)
    {
        if (!is_numeric($forum_id)) {
            $this->error('Forum parameter is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum does not exist.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic parameter is not numeric.');
        }

        $topic = $this->getManager()->getById($topic_id);

        if (!$topic) {
            $this->error('Topic does not exist.');
        }

        $data = $this->postsManager->getByTopicJoinedUser($topic_id);

        if ($this->topicSetting->canLogView()) {
            $this->getManager()->update($topic_id, ArrayHash::from(['topic_view_count%sql' => 'topic_view_count + 1']));
        }

        $pagination = new PaginatorControl($data, 10, 5, $page);
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $forum_id);
        }

        $user_id = $this->getUser()->getId();

        $this->template->avatarsDir = $this->avatars->getTemplateDir();
        $this->template->topicWatch = $this->topicWatchManager->fullCheck($topic_id, $user_id);
        $this->template->ranks      = $this->rankManager->getAllCached();
        $this->template->posts      = $data->orderBy('post_id', dibi::ASC)->fetchAll();
        $this->template->canThank   = $this->thanksManager->canUserThank($forum_id, $topic_id, $user_id);
        $this->template->thanks     = $this->thanksManager->getThanksJoinedUserByTopic($topic_id);
        $this->template->forum      = $forum;
        $this->template->topic      = $topic;
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderEdit($forum_id, $topic_id)
    {
    }
    
    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderReport($forum_id, $topic_id, $page)
    {
    }

    /**
     * @param int $topic_id
     */
    public function renderWatchers($topic_id)
    {
        $watchers = $this->topicWatchManager->getAllJoinedByLeft($topic_id);
        
        if (!$watchers) {
            $this->flashMessage('No watchers.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->watchers = $watchers;
    }
    
    /**
     *
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderThanks($forum_id, $topic_id)
    {
        $thanks = $this->thanksManager->getThanksJoinedUserByTopic($topic_id);
        
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

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextArea('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');
        
        $form->onSuccess[] = [$this,'editFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $forum_id = $this->getParameter('forum_id');
        $topic_id = $this->getParameter('topic_id');
        $user_id  = $this->getUser()->getId();

        $values->post_add_time = time();
        $values->post_user_id  = $user_id;
        $values->post_forum_id = $forum_id;
        $values->post_add_user_ip = $this->getHttpRequest()->getRemoteAddress();

        $topic_id = $this->topicFacade->add($values);

        if ($topic_id) {
            $this->flashMessage('Topic was saved.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Topic:default', $forum_id, $topic_id);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'params' => [$this->getParameter('forum_id')], 'text' => 'menu_forum'],
            2 => ['text' => 'menu_topic']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'params' => [$this->getParameter('forum_id')], 'text' => 'menu_forum'],
            2 => ['text' => 'menu_topic']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'params' => [$this->getParameter('forum_id')], 'text' => 'menu_forum'],
            2 => ['link'   => 'Topic:default',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')],
                  'text'   => 'menu_topic'
            ],
            3 => ['text' => 'report_topic']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbThanks()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'params' => [$this->getParameter('forum_id')], 'text' => 'menu_forum'],
            2 => ['link'   => 'Topic:default',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')],
                  'text'   => 'menu_topic'
            ],
            3 => ['text' => 'Thanks']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbWatchers()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'params' => [$this->getParameter('forum_id')], 'text' => 'menu_forum'],
            2 => ['link'   => 'Topic:default',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')],
                  'text'   => 'menu_topic'
            ],
            3 => ['text' => 'watches']
        ];

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
        return new TopicFastReplyForm($this->translatorFactory, $this->getUser(), $this->postFacade);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentReportForm()
    {
        $form = $this->getBootstrapForm();

        $form->addTextArea('report_text', 'Report text:');
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'reportFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function reportFormSuccess(Form $form, ArrayHash $values)
    {
        $forum_id = $this->getParameter('forum_id');
        $topic_id = $this->getParameter('topic_id');
        $post_id  = $this->getParameter('post_id');
        $page     = $this->getParameter('page');
        $user_id  = $this->getUser()->getId();

        $values->report_forum_id = $forum_id;
        $values->report_topic_id = $topic_id;
        $values->report_post_id  = $post_id;
        $values->report_user_id  = $user_id;
        $values->report_time     = time();

        $res = $this->reportManager->add($values);

        if ($res) {
            if ($post_id) {
                $this->flashMessage('Post was reported.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Topic was reported.', self::FLASH_MESSAGE_SUCCESS);
            }
        }

        $this->redirect('Topic:default', $forum_id, $topic_id, $page);
    }
}
