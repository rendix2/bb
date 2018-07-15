<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\JumpToForumControl;
use App\Controls\PaginatorControl;
use App\Models\ForumsManager;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use dibi;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendi
 * @method PostsManager getManager()
 */
class PostPresenter extends Base\ForumPresenter
{
    /**
     * @var ForumsManager $forumManager
     * @inject
     */
    public $forumManager;

    /**
     * @var ThanksManager $thanksManager
     * @inject
     */
    public $thanksManager;

    /**
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;

    /**
     * @var RanksManager $rankManager
     * @inject
     */
    public $rankManager;

    /**
     * @var TopicWatchManager $topicWatchManager
     * @inject
     */
    public $topicWatchManager;

    /**
     * @var ReportsManager $reportManager
     * @inject
     */
    public $reportManager;

    /**
     * @var \App\Controls\TopicsSetting $topicSetting
     * @inject
     */
    public $topicSetting;
    
    /**
     *
     * @var \App\Controls\Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     *
     * @var \App\Controls\BBMailer $bbMailer
     * @inject
     */
    public $bbMailer;
    
    /**
     * 
     * @var \App\Models\PostFacade $postFacade
     * @inject
     */
    public $postFacade;

    /**
     * 
     * @var \App\Models\ThanksFacade $thanksFacade
     * @inject
     */
    public $thanksFacade;
    
    /**
     * @var \App\Models\TopicFacade $topicFacade
     * @inject
     */
    public $topicFacade;

    /**
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * @return BootstrapForm
     */
    private function postForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextArea('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');

        return $form;
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     */
    public function actionDeletePost($forum_id, $topic_id, $post_id, $page)
    {
        if (!$this->getUser()
            ->isAllowed(
                $forum_id,
                'post_delete'
            )) {
            $this->error(
                'Not allowed.',
                IResponse::S403_FORBIDDEN
            );
        }

        $res = $this->postFacade->delete($post_id);

        if ($res === 1) {
            $this->flashMessage('Post deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Post:all', $forum_id, $topic_id, $page);
        } elseif ($res === 2) {
            $this->flashMessage('Topic deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Forum:default', $forum_id, $page);
        }        
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionDeleteTopic($forum_id, $topic_id, $page)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'topic_delete')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $res = $this->topicFacade->delete($topic_id);

        if ($res) {
            $this->flashMessage('Topic deleted.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Forum:default', $forum_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStartWatchTopic($forum_id, $topic_id, $page)
    {
        $user_id = $this->getUser()->getId();
        $res     = $this->topicWatchManager->addByLeft($topic_id, [$user_id]);

        if ($res) {
            $this->flashMessage('You have start watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Post:all', $forum_id, $topic_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function actionStopWatchTopic($forum_id, $topic_id, $page)
    {
        $user_id = $this->getUser()->getId();
        $res = $this->topicWatchManager->fullDelete($topic_id, $user_id);

        if ($res) {
            $this->flashMessage('You have stop watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Post:all', $forum_id, $topic_id, $page);
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
            $this->flashMessage('Your thank to this topic!', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Post:all', $forum_id, $topic_id);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderAll($forum_id, $topic_id, $page = 1)
    {
        if (!is_numeric($forum_id)) {
            $this->error('Forum parameter is not numeric.');
        }

        $forum = $this->forumManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum does not exist.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic parameter is not numeric.');
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic does not exist.');
        }

        $data = $this->getManager()->getByTopic($topic_id);

        if ($this->topicSetting->canLogView()) {
            $this->topicsManager->update($topic_id, ArrayHash::from(['topic_view_count%sql' => 'topic_view_count + 1']));
        }

        $pagination = new PaginatorControl($data, 10, 5, $page);
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $forum_id);
        }

        $user_id = $this->getUser()->getId();

        $this->template->avatarsDir = $this->avatar->getTemplateDir();        
        $this->template->topicWatch = $this->topicWatchManager->fullCheck($topic_id, $user_id);
        $this->template->ranks      = $this->rankManager->getAllCached();
        $this->template->posts      = $data->orderBy('post_id', dibi::ASC)->fetchAll();
        $this->template->canThank   = $this->thanksManager->canUserThank($forum_id, $topic_id, $user_id);
        $this->template->thanks     = $this->thanksManager->getThanksWithUserInTopic($topic_id);
        $this->template->forum      = $forum;
        $this->template->topic      = $topic;
    }

    /**
     *
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public function renderEditPost($forum_id, $topic_id, $post_id = null)
    {
        if ($post_id === null) {
            if (!$this->getUser()->isAllowed($forum_id, 'post_add')) {
                $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
            }
        } else {
            if (!$this->getUser()->isAllowed($forum_id, 'post_update')) {
                $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
            }
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic does not exist.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }

        $post = [];

        if ($post_id) {
            $post = $this->getManager()->getById($post_id);

            if ($post->post_locked) {
                $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
            }
        }

        $this['editPostForm']->setDefaults($post);
    }

    /**
     *
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderEditTopic($forum_id, $topic_id = null)
    {
        if ($topic_id === null) {
            if (!$this->getUser()->isAllowed($forum_id, 'topic_add')) {
                $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
            }
        } else {
            if (!$this->getUser()->isAllowed($forum_id, 'topic_edit')) {
                $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
            }
        }

        $topic = [];

        if ($topic_id) {
            $topic = $this->topicsManager->getById($topic_id);
        }

        $this['editTopicForm']->setDefaults($topic);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     */
    public function renderReportPost($forum_id, $topic_id, $post_id, $page)
    {
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $page
     */
    public function renderReportTopic($forum_id, $topic_id, $page)
    {
    }

    /**
     * @param int $topic_id
     */
    public function renderWatchers($topic_id)
    {
        $this->template->watchers = $this->topicWatchManager->getAllJoinedByLeft($topic_id);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditPostForm()
    {
        $form = $this->postForm();

        $form->onSuccess[] = [$this, 'editPostFormSuccess'];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditTopicForm()
    {
        $form = $this->postForm();

        $form->onSuccess[] = [$this,'editTopicFormSuccess'];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentFastReply()
    {
        $form = $this->getBootstrapForm();

        $form->addGroup('Fast reply');
        $form->addTextArea('post_text');
        $form->addSubmit('send', 'Save');

        $form->onSuccess[] = [$this, 'fastReplySuccess'];

        return $form;
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
     * @return JumpToForumControl
     */
    public function createComponentJumpToForum()
    {
        return new JumpToForumControl($this->forumManager);
    }    
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editPostFormSuccess(Form $form, ArrayHash $values)
    {
        $forum_id = $this->getParameter('forum_id');
        $post_id  = $this->getParameter('post_id');
        $topic_id = $this->getParameter('topic_id');
        $user_id  = $this->getUser()->getId();

        if ($post_id) {
            $values['post_edit_count%sql'] = 'post_edit_count + 1';
            $values->post_last_edit_time   = time();
            $values->post_edit_user_ip     = $this->getHttpRequest()->getRemoteAddress();

            $result = $this->getManager()->update($post_id, $values);
        } else {
            $values->post_forum_id    = $forum_id;
            $values->post_user_id     = $user_id;
            $values->post_topic_id    = $topic_id;
            $values->post_add_time    = time();
            $values->post_add_user_ip = $this->getHttpRequest()->getRemoteAddress();

            $result = $this->postFacade->add($values);
            
            $emails = $this->topicWatchManager->getAllJoinedByLeft($topic_id);
            
            $emailsArray = [];
            
            foreach ($emails as $email) {
                if ($this->getUser()->getIdentity()->getId() === $email->user_id) {
                    continue;
                }
                
                $emailsArray[] = $email->user_email;
            }
            
            if (count($emailsArray)) {
                $this->bbMailer->addRecepients($emailsArray);
                $this->bbMailer->setSubject('Topic watch');
                $this->bbMailer->setText('Test');
                $this->bbMailer->send();
            }
        }

        if ($result) {
            $this->flashMessage('Post saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('Post:all', $forum_id, $topic_id);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editTopicFormSuccess(Form $form, ArrayHash $values)
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
            $this->flashMessage('Topic saved.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Post:all', $forum_id, $topic_id);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function fastReplySuccess(Form $form, ArrayHash $values)
    {
        $forum_id = $this->getParameter('forum_id');
        $topic_id = $this->getParameter('topic_id');
        $page     = $this->getParameter('page');

        $values->post_forum_id = $forum_id;
        $values->post_topic_id = $topic_id;
        $values->post_user_id  = $this->getUser()->getId();

        $res = $this->postFacade->add($values);

        if ($res) {
            $this->flashMessage('Post was added.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Post:all', $forum_id, $topic_id, $page);
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

        $this->redirect('Post:all', $forum_id, $topic_id, $page);
    }    
}
