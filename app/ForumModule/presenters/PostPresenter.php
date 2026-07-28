<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Controls\BBMailer;
use App\Controls\BreadCrumbControl;
use App\Database\EntityManagerDecorator;
use App\Forms\ReportForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Models\CategoryManager;
use App\Models\Entity\FileEntity;
use App\Models\Entity\PollEntity;
use App\Models\Entity\PostEntity;
use App\Models\Manager;
use App\Models\PollsFacade;
use App\Models\PostFacade;
use App\Models\Posts2FilesManager;
use App\Models\PostsHistoryManager;
use App\Models\PostManager;
use App\Models\ReportManager;
use App\Settings\PostSetting;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostManager getManager()
 * @package App\ForumModule\Presenters
 */
class PostPresenter extends BaseForumPresenter
{
    /**
     * @var ReportManager $reportManager
     * @inject
     */
    public ReportManager $reportManager;
    
    /**
     *
     * @var BBMailer $bbMailer
     * @inject
     */
    public BBMailer $bbMailer;
    
    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public PostFacade $postFacade;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     * @inject
     */
    public PostsHistoryManager $postsHistoryManager;
    
    /**
     * @var PostSetting $postSetting
     * @inject
     */
    public PostSetting $postSetting;

    /**
     * @var IStorage $storage
     * @inject
     */
    public IStorage $storage;
    
    /**
     *
     * @var PollsFacade $pollsFacade
     * @inject
     */
    public PollsFacade $pollsFacade;
    
    /**
     *
     * @var Posts2FilesManager $posts2FilesManager
     * @inject
     */
    public Posts2FilesManager $posts2FilesManager;

    /**
     * PostPresenter constructor.
     *
     * @param PostManager $manager
     * @param EntityManagerDecorator $em
     */
    public function __construct(

        PostManager $manager,
        private readonly CategoryManager $categoryManager,
        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct($manager);
    }

    /**
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $page
     * @throws \Exception
     */
    public function actionDelete($category_id, $forum_id, $topic_id, $post_id, $page): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
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

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $post     = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id);
        
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
        $postScope  = $this->loadPost($forum, $topic, $post);
        
        $this->requireAccess($postScope, PostScope::ACTION_DELETE);

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
     * @param int      $category_id
     * @param int      $forum_id
     * @param int      $topic_id
     * @param int|null $post_id
     */
    public function renderEdit($category_id, $forum_id, $topic_id, $post_id = null)
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
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

        $forum      = $this->checkForumParam($forum_id, $category_id);
        $topic      = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $forumScope = $this->loadForum($forum);
        
        if ($post_id === null) {
            $this->requireAccess($forumScope, ForumScope::ACTION_POST_ADD);
        } else {
            $this->requireAccess($forumScope, ForumScope::ACTION_POST_UPDATE);
        }

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
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
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
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
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
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $file_id
     */
    public function actionDownloadFile($category_id, $forum_id, $topic_id, $post_id, $file_id)
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
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

        $forum    = $this->checkForumParam($forum_id, $category_id);
        $topic    = $this->checkTopicParam($topic_id, $category_id, $forum_id);
        $post     = $this->checkPostParam($post_id, $category_id, $forum_id, $topic_id);
        
        $file = $this->posts2FilesManager->getFullJoined($post_id, $file_id);
        
        if (!$file) {
            $this->error('File was not found.');
        }
        
        $sep = DIRECTORY_SEPARATOR;
        
        $fileResponse = new FileResponse(
            $this->postSetting->get()['filesDir'] . $sep . $file->file_name . '.' . $file->file_extension,
            $file->file_orig_name
        );
        
        $this->sendResponse($fileResponse);
    }
    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextAreaHtml('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');
        $form->addSubmit('preview', 'Preview')->onClick[] = [$this, 'preview'];
        
        $files = $form->addDynamic('files', function (Container $file) {
            $file->addHidden('post_file_id');
            $file->addUpload('post_file', 'File:');
            $file->addSubmit('remove', 'Remove file')
                   ->setValidationScope(false) # disables validation
                   ->addRemoveOnClick();
        }, 1);
        $files->addSubmit('add', 'Add file')
                ->setValidationScope(false) # disables validation
                ->addCreateOnClick(true);
        
        $form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        $form->onSuccess[]  = [$this, 'editFormSuccess'];
        $form->onValidate[] = [$this, 'editFormOnValidate'];

        return $form;
    }
    
    /**
     *
     * @param SubmitButton $submit
     * @param ArrayHash    $values
     */
    public function preview(SubmitButton $submit, ArrayHash $values): void
    {
        $this['editForm']->setDefaults($values);
        $this->getTemplate()->preview_text = $this['editForm-post_text']->getValue();
            
        $submit->getForm()->addError('Post was not saved. You see preview.');
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormOnValidate(Form $form, ArrayHash $values): void
    {
        $user_id = $this->getUser()->getId();

        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $minTimeInterval    = $this->postSetting->get()['minUserTimeInterval'];
        $doublePostInterval = $this->postSetting->get()['minDoublePostTimeInterval'];

        if (time() - $userEntity->user_last_post_time <= $minTimeInterval) {
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
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $post_id     = $this->getParameter('post_id');
        $user_id     = $this->getUser()->getId();
        
        if (count($values->files)) {
            $postFiles = [];
            $filesDir = $this->postSetting->get()['filesDir'];
            
            /**
             * @var FileUpload $file
            */
            foreach ($values->files as $file) {
                $postFileArrayHash = $file->post_file;
                
                $extension = Manager::getFileExtension($postFileArrayHash->getName());
                $hash      = Random::generate(32);
                $sep       = DIRECTORY_SEPARATOR;
                
                if ($postFileArrayHash->isOk()) {
                    $postFileArrayHash->move($filesDir . $sep .$hash . '.'. $extension);
                }
                
                $postFile = new FileEntity();
                $postFile->setFile_id($file->post_file_id);
                $postFile->setFile_orig_name($postFileArrayHash->getName());
                $postFile->setFile_name($hash);
                $postFile->setFile_extension($extension);
                $postFile->setFile_size($postFileArrayHash->getSize());
                
                $postFiles[] = $postFile;
            }
        } else {
            $postFiles = [];
        }
        
        if ($post_id) {
            $postOldDibi = $this->getManager()->getById($post_id);
            $postOld     = PostEntity::setFromRow($postOldDibi);
            
            $postNew = new PostEntity();
            $postNew->setPost_id($post_id)
                    ->setPost_user_id($postOld->getPost_user_id())
                    ->setPost_category_id($category_id)
                    ->setPost_forum_id($forum_id)
                    ->setPost_topic_id($topic_id)
                    ->setPost_title($values->post_title)
                    ->setPost_text($values->post_text)
                    ->setPost_add_time($postOld->getPost_add_time())
                    ->setPost_add_user_ip($postOld->getPost_add_user_ip())
                    ->setPost_edit_user_ip($this->getHttpRequest()->getRemoteAddress())
                    ->setPost_edit_count($postOld->getPost_edit_count() + 1)
                    ->setPost_last_edit_time(time())
                    ->setPost_locked($postNew->getPost_locked())
                    ->setPost_order($postOld->getPost_order())
                    ->setPost_files($postFiles);
                                          
            $result = $this->postFacade->update($postNew);
        } else {
            $post = new PostEntity();
            $post->setPost_user_id($user_id)
                 ->setPost_category_id($category_id)
                 ->setPost_forum_id($forum_id)
                 ->setPost_topic_id($topic_id)
                 ->setPost_title($values->post_title)
                 ->setPost_text($values->post_text)
                 ->setPost_add_user_ip($this->getHttpRequest()->getRemoteAddress())
                 ->setPost_order(1)
                 ->setPost_files($postFiles);

            $result = $this->postFacade->add($post);

            $emails = $this->em
                ->getRepository(TopicWatchEntity::class)
                ->createQueryBuilder('_tw')

                ->addSelect('_user')
                ->leftJoin('_tw.user', '_user')

                ->where('_tw.topic = :topic')
                ->setParameter('topic', $topic_id)

                ->getQuery()
                ->getResult();
            
            $emailsArray = [];
            
            foreach ($emails as $email) {
                if ($user_id === $email->user->id) {
                    continue;
                }
                
                $emailsArray[] = $email->user->id;
            }
            
            if (count($emailsArray)) {
                $this->bbMailer->addRecipients($emailsArray);
                $this->bbMailer->setSubject($this->getTranslator()->translate('topic_watch_mail_subject'));
                $this->bbMailer->setText(
                    sprintf(
                        $this->getTranslator()->translate('topic_watch_mail_text'),
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

    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'text' => 'menu_topic',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ]
            ]],
            [['text' => 'menu_post']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'text' => 'menu_topic',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ]
            ]],
            [['text' => 'report_post']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    protected function createComponentBreadCrumbHistory(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->forumsManager->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Topic:default',
                'text' => 'menu_topic',
                'params' => [
                    $this->getParameter('category_id'),
                    $this->getParameter('forum_id'),
                    $this->getParameter('topic_id')
                ]
            ]],
            [['text' => 'post_history']]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    protected function createComponentReportForm(): ReportForm
    {
        return new ReportForm($this->reportManager);
    }
}
