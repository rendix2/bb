<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Forms\ReportForm;
use App\Models\PostFacade;
use App\Models\PostsHistoryManager;
use App\Models\PostsManager;
use App\Models\ReportsManager;
use App\Models\TopicWatchManager;
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
        if (!$this->getUser()->isAllowed($forum_id,'post_delete')) {
            $this->error('Not allowed.',IResponse::S403_FORBIDDEN);
        }

        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $post     = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id);

        $res = $this->postFacade->delete($topic, $post);

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

        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);

        // post check
        $post = [];

        if ($post_id) {
            $post = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id)->getArray();
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
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $post     = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id);
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
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $post     = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id);

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
            $postOldDibi = $this->getManager()->getById($post_id);
            $postOld     = \App\Models\Entity\Post::get($postOldDibi);
            
            $postNew  = new \App\Models\Entity\Post(
                $post_id, 
                $user_id, 
                $category_id, 
                $forum_id, 
                $topic_id, 
                $values->post_title, 
                $values->post_text,
                $postOld->post_add_time,
                $postOld->post_add_user_ip,
                $this->getHttpRequest()->getRemoteAddress(),
                $postOld->post_edit_count + 1, 
                time(), 
                $postOld->post_locked, 
                $postOld->post_order
            );
                                          
            $result = $this->postFacade->update($postNew);
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
                1
            );
            
            $result = $this->postFacade->add($post);
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
     * @return BootstrapForm
     */
    protected function createComponentReportForm()
    {
        return new ReportForm($this->reportManager);
    }    
}
