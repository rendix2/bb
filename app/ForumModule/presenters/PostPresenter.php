<?php

namespace App\ForumModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Models\PostFacade;
use App\Models\PostsHistoryManager;
use App\Models\PostsManager;
use App\Models\ReportsManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Models\UsersManager;
use App\Settings\PostSetting;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostsManager getManager()
 */
class PostPresenter extends Base\ForumPresenter
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
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     */
    public function actionDelete($forum_id, $topic_id, $post_id, $page)
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
        
        $post = $this->getManager()->getById($post_id);
        
        if (!$post) {
            $this->error('Post was not found.');
        }
        
        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
        }

        $res = $this->postFacade->delete($post_id);

        if ($res === 1) {
            $this->flashMessage('Post was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Topic:default', $forum_id, $topic_id, $page);
        } elseif ($res === 2) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Forum:default', $forum_id, $page);
        }
    }

    /**
     *
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public function renderEdit($forum_id, $topic_id, $post_id = null)
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
            
            if ($post->post_user_id !== $this->getUser()->getId()) {
                $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
            }
        }        

        $this['editForm']->setDefaults($post);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     */
    public function renderReport($forum_id, $topic_id, $post_id, $page)
    {
    }
    
    /**
     *
     * @param int $post_id
     */
    public function renderHistory($post_id)
    {
        $user_id = $this->getUser()->getId();
        
        $post = $this->getManager()->getById($post_id);
        
        if (!$post) {
            $this->error('Post was not found.');
        }
        
        if ($post->post_user_id !== $user_id) {
            $this->error('You are not author of post.');
        }
        
        $this->template->posts = $this->postsHistoryManager->getJoinedByPost($post_id);
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
        $forum_id = $this->getParameter('forum_id');
        $post_id  = $this->getParameter('post_id');
        $topic_id = $this->getParameter('topic_id');
        $user_id  = $this->getUser()->getId();

        if ($post_id) {
            $values['post_edit_count%sql'] = 'post_edit_count + 1';
            $values->post_last_edit_time   = time();
            $values->post_edit_user_ip     = $this->getHttpRequest()->getRemoteAddress();
            $values->post_user_id          = $user_id;

            $result = $this->postFacade->update($post_id, $values);
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
                $this->bbMailer->addRecipients($emailsArray);
                $this->bbMailer->setSubject('Topic watch');
                $this->bbMailer->setText('Test');
                $this->bbMailer->send();
            }
        }

        if ($result) {
            $this->flashMessage('Post was saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('Topic:default', $forum_id, $topic_id);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'text' => 'menu_forum', 'params' => [$this->getParameter('forum_id')]],
            2 => ['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')]
            ],
            3 => ['text' => 'menu_post']

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
            1 => ['link' => 'Forum:default', 'text' => 'menu_forum', 'params' => [$this->getParameter('forum_id')]],
            2 => ['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')]
            ],
            3 => ['text' => 'report_post']

        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbHistory()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'text' => 'menu_forum', 'params' => [$this->getParameter('forum_id')]],
            2 => ['link'   => 'Topic:default',
                  'text'   => 'menu_topic',
                  'params' => [$this->getParameter('forum_id'), $this->getParameter('topic_id')]
            ],
            3 => ['text' => 'post_history']

        ];

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
