<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\TopicJumpToForumForm;
use App\Controls\PaginatorControl;
use App\Models\ForumsManager;
use App\Models\PostsManager;
use App\Models\RanksManager;
use App\Models\ReportsManager;
use App\Models\ThanksManager;
use App\Models\TopicsManager;
use App\Models\TopicWatchManager;
use App\Settings\Avatars;
use App\Settings\TopicsSetting;
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
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextArea('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');

        $form->onSuccess[] = [$this, 'editPostFormSuccess'];

        return $form;
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
            $this->flashMessage('Post was saved.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('Topic:default', $forum_id, $topic_id);
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
