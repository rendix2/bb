<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\PaginatorControl;
use App\Models\ForumsManager;
use App\Models\PostsManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
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
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @var ForumsManager $forumManager
     */
    private $forumManager;

    /**
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;

    /**
     * @var TopicsManager $topicsManager
     */
    private $topicsManager;
    
    private $rankManager;
    
    private $topicWatchManager;
    
    private $reportManager;

    /**
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }
    
 /**
     * @param ForumsManager $forumsManager
     */
    public function injectForumsManager(ForumsManager $forumsManager)
    {
        $this->forumManager = $forumsManager;
    }

    /**
     * @param ThanksManager $thanksManager
     */
    public function injectThanksManager(ThanksManager $thanksManager)
    {
        $this->thanksManager = $thanksManager;
    }

    /**
     * @param TopicsManager $topicsManager
     */
    public function injectTopicsManager(TopicsManager $topicsManager)
    {
        $this->topicsManager = $topicsManager;
    }

    /**
     * @param UsersManager $usersManager
     */
    public function injectUsersManager(UsersManager $usersManager)
    {
        $this->userManager = $usersManager;
    }    
    
    public function injectRanksManager(\App\Models\RanksManager $rankManager){
        $this->rankManager = $rankManager;
    }
    
    public function injectTopicsWatchManager(\App\Models\TopicWatchManager $topicWatchManager){
        $this->topicWatchManager =  $topicWatchManager;
    } 
    
    public function injectReportManager(\App\Models\ReportsManager $reportManager){
        $this->reportManager = $reportManager;
    }

        public function actionStopWatchTopic($forum_id, $topic_id, $page){
        $res = $this->topicWatchManager->fullDelete($topic_id, $this->getUser()->getId());
        
        if ($res){
            $this->flashMessage('You have stop watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Post:all', $forum_id, $topic_id, $page);
    }
    
    public function actionStartWatchTopic($forum_id, $topic_id, $page){
       $res = $this->topicWatchManager->addByLeft($topic_id, [$this->getUser()->getId()]);
        
                if ($res){
            $this->flashMessage('You have start watching topic.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Post:all', $forum_id, $topic_id, $page);
    }    
    
    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public function actionDeletePost($forum_id, $topic_id, $post_id, $page)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'post_delete')) {
            $this->error('Not allowed.');
        }

        $this->getManager()->delete($post_id);

        $this->flashMessage('Post deleted.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Post:all', $forum_id, $topic_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param tint $page
     */
    public function actionDeleteTopic($forum_id, $topic_id, $page)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'topic_delete')) {
            $this->error('Not allowed');
        }

        $this->topicsManager->delete($topic_id);

        $this->flashMessage('Topic deleted.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Forum:default', $forum_id, $page);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     */
    public function actionThank($forum_id, $topic_id)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'topic_thank')) {
            $this->error('Not allowed');
        }

        $user_id = $this->getUser()->getId();

        $data = [
            'thank_forum_id' => $forum_id,
            'thank_topic_id' => $topic_id,
            'thank_user_id'  => $user_id,
            'thank_time'     => time()
        ];

        $this->thanksManager->add(ArrayHash::from($data));

        $this->flashMessage('Your thank to this topic!', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Post:all', $forum_id, $topic_id);
    }    

    /**
     * @return BootstrapForm
     */
    private function postForm()
    {
        $form = new BootstrapForm();
        $form->setTranslator($this->getForumTranslator());

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextArea('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');

        return $form;
    }

    /**
     * @param int      $forum_id
     * @param int      $topic_id
     * @param int      $page
     */
    public function renderAll($forum_id, $topic_id, $page = 1)
    {
        $data = $this->getManager()->getPostsByTopicId($topic_id);

        $pagination = new PaginatorControl($data, 10, 5, $page);
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
            $this->redirect('Forum:default', $forum_id);
        }
                
        $this->template->topicWatch = $this->topicWatchManager->fullCheck($topic_id, $this->getUser()->getId());
        $this->template->ranks      = $this->rankManager->getAllCached();
        $this->template->posts      = $data->orderBy('post_id', \dibi::DESC)->fetchAll();
        $this->template->topic      = $this->topicsManager->getById($topic_id);
        $this->template->canThank   = $this->thanksManager->canUserThank($forum_id, $topic_id, $this->getUser()->getId());
        $this->template->thanks     = $this->thanksManager->getThanksWithUserInTopic($topic_id);
        $this->template->forum      = $this->forumManager->getById($forum_id);
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
                $this->error('Not allowed');
            }
        } else {
            if (!$this->getUser()->isAllowed($forum_id, 'post_update')) {
                $this->error('Not allowed');
            }
        }

        $post = [];

        if ($post_id) {
            $post = $this->getManager()->getById($post_id);
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
                $this->error('Not allowed');
            }
        } else {
            if (!$this->getUser()->isAllowed($forum_id, 'topic_edit')) {
                $this->error('Not allowed');
            }
        }

        $topic = [];

        if ($topic_id) {
            $topic = $this->topicsManager->getById($topic_id);
        }

        $this['editTopicForm']->setDefaults($topic);
    }
    
    public function renderWatchers($topic_id){
        $this->template->watchers = $this->topicWatchManager->getByLeftJoined($topic_id);
    }
    
    public function renderReport($forum_id, $topic_id, $post_id, $page){
        
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditPostForm()
    {
        $form = $this->postForm();

        $form->onSuccess[] = [
            $this,
            'editPostFormSuccess'
        ];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditTopicForm()
    {
        $form = $this->postForm();

        $form->onSuccess[] = [
            $this,
            'editTopicFormSuccess'
        ];

        return $form;
    }
    
    protected function createComponentReportForm()
    {
        $form = $this->getBootStrapForm();
        
        $form->addTextArea('report_text', 'Report text:');
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'reportFormSuccess'];
        
        return $form;        
    }
    
    public function reportFormSuccess(Form $form, ArrayHash $values){
        $forum_id = $this->getParameter('forum_id');
        $topic_id = $this->getParameter('topic_id');
        $post_id  = $this->getParameter('post_id');
        $page     = $this->getParameter('page');
        $user_id  = $this->getUser()->getId();
                
        $values->report_forum_id = $forum_id;
        $values->report_topic_id = $topic_id;
        $values->report_post_id  = $post_id;
        $values->report_user_id  = $user_id;
                
        $res = $this->reportManager->add($values);
        
        if ( $res ){
            $this->flashMessage('Post was reported.', self::FLASH_MESSAGE_SUCCESS);
        }
        
        $this->redirect('Post:all', $forum_id, $topic_id, $page);      
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

            $result = $this->getManager()->update($post_id, $values);
        } else {
            $values->post_forum_id = $forum_id;
            $values->post_user_id = $user_id;
            $values->post_topic_id = $topic_id;
            $values->post_add_time = time();

            $result = $this->getManager()->add($values);
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

        $topic_id = $this->topicsManager->add($values);
        
        $this->flashMessage('Topic saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('Post:all', $forum_id, $topic_id);
    }    
}
