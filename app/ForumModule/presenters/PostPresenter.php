<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\PostFacade;
use App\Models\PostsHistoryManager;
use App\Models\PostsManager;
use App\Models\ReportsManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Models\UsersManager;
use App\Settings\PostSetting;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostsManager getManager()
 */
class PostPresenter extends \App\ForumModule\Presenters\Base\ForumPresenter
{

    /**
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;

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
     *
     * @var BBMailer $bbMailer
     * @inject
     */
    public $bbMailer;
    
    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public $postFacade;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     * @inject
     */
    public $postsHistoryManager;
    
    /**
     * @var PostSetting $postSetting
     * @inject
     */
    public $postSetting;
    
    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;

    /**
     * @var ForumsManager $forumManager
     * @inject
     */
    public $forumsManager;

    /**
     * @var CategoriesManager
     * @inject
     */
    public $categoriesManager;

    /**
     * @var IStorage $storage
     * @inject
     */
    public $storage;

    /**
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function actionDelete($category_id, $forum_id, $topic_id, $post_id, $page)
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

        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $category = $this->categoriesManager->getById($category_id);

        if (!$category) {
            $this->error('Category was not found.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($forum->forum_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic was not found.');
        }

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }

        $post = $this->getManager()->getById($post_id);

        if (!$post) {
            $this->error('Post was not found.');
        }

        if ($post->post_category_id !== (int)$category_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_forum_id !== (int)$forum_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_topic_id !== (int)$topic_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_locked) {
            $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
        }

        $res = $this->postFacade->delete($post_id);

        if ($res === 1) {
            $this->flashMessage('Post was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
        } elseif ($res === 2) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Forum:default', $category_id, $forum_id, $page);
        }
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int|null $post_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id, $post_id = null)
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

        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $category = $this->categoriesManager->getById($category_id);

        if (!$category) {
            $this->error('Category was not found.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($forum->forum_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic was not found.');
        }

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }

        // post check
        $post = [];

        if ($post_id) {
            $post = $this->getManager()->getById($post_id);

            if (!$post) {
                $this->error('Post was not found.');
            }

            if ($post->post_category_id !== (int)$category_id) {
                $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
            }

            if ($post->post_forum_id !== (int)$forum_id) {
                $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
            }

            if ($post->post_topic_id !== (int)$topic_id) {
                $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
            }

            if ($post->post_user_id !== $this->getUser()->getId()) {
                $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
            }

            if ($post->post_locked) {
                $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
            }
        }

        $this['editForm']->setDefaults($post);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     */
    public function renderReport($category_id, $forum_id, $topic_id, $post_id, $page)
    {
        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $category = $this->categoriesManager->getById($category_id);

        if (!$category) {
            $this->error('Category was not found.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($forum->forum_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic was not found.');
        }

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }

        $post = $this->getManager()->getById($post_id);

        if (!$post) {
            $this->error('Post was not found.');
        }

        if ($post->post_category_id !== (int)$category_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_forum_id !== (int)$forum_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_topic_id !== (int)$topic_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_locked) {
            $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
        }
    }

    /**
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public function renderHistory($category_id, $forum_id, $topic_id, $post_id)
    {
        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $category = $this->categoriesManager->getById($category_id);

        if (!$category) {
            $this->error('Category was not found.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($forum->forum_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topic = $this->topicsManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic was not found.');
        }

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }

        $post = $this->getManager()->getById($post_id);

        if (!$post) {
            $this->error('Post was not found.');
        }

        if ($post->post_category_id !== (int)$category_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_forum_id !== (int)$forum_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_topic_id !== (int)$topic_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_locked) {
            $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
        }

        $postHistory = $this->postsHistoryManager->getByPost($post_id);

        if (!$postHistory) {
            $this->flashMessage('Post have no history.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $postHistory;
    }
    
    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextAreaHtml('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');
        $form->addSubmit('preview', 'Preview')->onClick[] = [$this, 'preview'];

        $form->onSuccess[]  = [$this, 'editFormSuccess'];
        $form->onValidate[] = [$this, 'onValidate'];

        return $form;
    }
    
    /**
     *
     * @param SubmitButton $submit
     * @param ArrayHash $values
     */
    public function preview(SubmitButton $submit, ArrayHash $values)
    {
        $this['editForm']->setDefaults($values);
        $this->template->preview_text = $this['editForm-post_text']->getValue();
            
        $submit->getForm()->addError('Post was not saved. You see preview.');
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function onValidate(Form $form, ArrayHash $values)
    {
        $user_id            = $this->getUser()->getId();
        $user               = $this->usersManager->getById($user_id);
        $minTimeInterval    = $this->postSetting->get()['minUserTimeInterval'];
        $doublePostInterval = $this->postSetting->get()['minDoublePostTimeInterval'];

        if (time() - $user->user_last_post_time <= $minTimeInterval) {
            $form->addError('You cannot send new post so soon.', false);
        }

        if ($this->getManager()->checkDoublePost($values->post_text, $user_id, time() - $doublePostInterval)) {
            $form->addError('Double post', false);
        }
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $post_id     = $this->getParameter('post_id');
        $topic_id    = $this->getParameter('topic_id');
        $user_id     = $this->getUser()->getId();

        if ($post_id) {
            $values['post_edit_count%sql'] = 'post_edit_count + 1';
            $values->post_last_edit_time   = time();
            $values->post_edit_user_ip     = $this->getHttpRequest()->getRemoteAddress();
            $values->post_user_id          = $user_id;

            $result = $this->postFacade->update($post_id, $values);
        } else {
            $values->post_category_id = $category_id;
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
                $this->bbMailer->addRecipients($emailsArray);
                $this->bbMailer->setSubject($this->getForumTranslator()->translate('topic_watch_mail_subject'));
                $this->bbMailer->setText(
                    sprintf(
                        $this->getForumTranslator()->translate('topic_watch_mail_text'),
                        $this->link('//Topic:default', $category_id, $forum_id, $topic_id)
                    )
                );
                $this->bbMailer->send();
            }

            // refresh cache on index page to show this last topic
            $cache = new Cache($this->storage, IndexPresenter::CACHE_NAMESPACE);
            $cache->remove(IndexPresenter::CACHE_KEY_LAST_POST);
            $cache->remove(IndexPresenter::CACHE_KEY_TOTAL_POSTS);
        }

        if ($result) {
            $this->flashMessage('Post was saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id);
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
                [['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ]
                ]],
                [['text' => 'menu_post']]
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
                $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
                [['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ]
                ]],
                [['text' => 'report_post']]
        );         

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbHistory()
    {
        $breadCrumb = array_merge(
                [['link' => 'Index:default', 'text' => 'menu_index']],
                $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
                $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
                [['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [
                      $this->getParameter('category_id'),
                      $this->getParameter('forum_id'),
                      $this->getParameter('topic_id')
                  ]
                ]],
                [['text' => 'post_history']]
        );           

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     *
     * REPORT FORM
     */
    
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
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $post_id     = $this->getParameter('post_id');
        $page        = $this->getParameter('page');
        $user_id     = $this->getUser()->getId();

        $values->report_forum_id = $forum_id;
        $values->report_topic_id = $topic_id;
        $values->report_post_id  = $post_id;
        $values->report_user_id  = $user_id;
        $values->report_time     = time();

        $res = $this->reportManager->add($values);

        if ($res) {
            $this->flashMessage('Post was reported.', self::FLASH_MESSAGE_SUCCESS);
        }

        $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }
}
