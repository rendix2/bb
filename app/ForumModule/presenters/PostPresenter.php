<?php

namespace App\ForumModule\Presenters;

use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Controls\BBMailer;
use App\Controls\BreadCrumbControl;
use App\Database\EntityManagerDecorator;
use App\Forms\ReportForm;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\PostHistoryEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Model\Repository\UserRepository;
use App\Models\Manager;
use App\Models\PostFacade;
use App\Models\Posts2FilesManager;
use App\Models\PostManager;
use App\services\BreadcrumbService;
use App\services\ScopeService;
use App\Settings\PostSetting;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\FileUpload;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostManager getManager()
 * @package App\ForumModule\Presenters
 */
class PostPresenter extends Presenter
{

    
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
     * @var PostSetting $postSetting
     * @inject
     */
    public PostSetting $postSetting;
    
    /**
     *
     * @var Posts2FilesManager $posts2FilesManager
     * @inject
     */
    public Posts2FilesManager $posts2FilesManager;

    public function __construct(

        PostManager $manager,
        private readonly EntityManagerDecorator $em,

        private readonly Translator $translator,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
        private readonly TopicRepository    $topicRepository,
        private readonly PostRepository     $postRepository,

        private readonly UserRepository       $userRepository,
        private readonly TopicWatchRepository $topicWatchRepository,

        private readonly ScopeService      $scopeService,
        private readonly BreadcrumbService $breadcrumbService,

        private readonly ReportForm $reportForm,
    )
    {
        parent::__construct($manager);
    }

    public function actionDelete(int $category_id, int $forum_id, int $topic_id, int $post_id, int $page): void
    {
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id
                ]
            );

        if ($forumEntity === null) {
            $this->error('Forum was not found.');
        }

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id
                ]
            );

        if ($topicEntity === null) {
            $this->error('Topic was not found.');
        }

        $postEntity = $this->postRepository
            ->findOneBy(
                [
                    'id' => $post_id
                ]
            );

        if ($postEntity === null) {
            $this->error('Post was not found.');
        }

        $postScope = $this->scopeService->loadPost($forumEntity, $topicEntity, $postEntity);
        
        $this->requireAccess($postScope, PostScope::ACTION_DELETE);

        $res = $this->postFacade->delete($topicEntity, $postEntity);

        if ($res === 1) {
            $this->flashMessage('Post was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
        } elseif ($res === 2) {
            $this->flashMessage('Topic was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Forum:default', $category_id, $forum_id, $page);
        }
    }

    public function renderEdit($category_id, $forum_id, $topic_id, $post_id = null)
    {
        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $forumScope = $this->scopeService->loadForum($forumEntity);
        
        if ($post_id === null) {
            $this->requireAccess($forumScope, ForumScope::ACTION_POST_ADD);
        } else {
            $this->requireAccess($forumScope, ForumScope::ACTION_POST_UPDATE);
        }

        if ($post_id) {
            $postEntity = $this->postRepository
                ->findOneBy(
                    [
                        'id' => $post_id,
                    ]
                );

            $this['editForm']->setDefaults($postEntity);
        }
    }

    public function renderReport($category_id, $forum_id, $topic_id, $post_id, $page)
    {
        $categoryEntity = $this->categoryRepository
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
    }

    public function renderHistory($category_id, $forum_id, $topic_id, $post_id)
    {
        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $postEntity = $this->postRepository->findOneBy(
            [
                'id' => $post_id,
            ]
        );

        if (!$postEntity->historyPosts->count()) {
            $this->flashMessage('Post have no history.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $postEntity->historyPosts;
    }

    public function actionDownloadFile($category_id, $forum_id, $topic_id, $post_id, $file_id)
    {
        $categoryEntity = $this->categoryRepository
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
    
    public function preview(SubmitButton $submit, ArrayHash $values): void
    {
        $this['editForm']->setDefaults($values);
        $this->getTemplate()->preview_text = $this['editForm-post_text']->getValue();
            
        $submit->getForm()->addError('Post was not saved. You see preview.');
    }

    public function editFormOnValidate(Form $form, ArrayHash $values): void
    {
        $user_id = $this->getUser()->getId();

        $userEntity = $this->userRepository
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

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $category_id = $this->getParameter('category_id');
        $forum_id    = $this->getParameter('forum_id');
        $topic_id    = $this->getParameter('topic_id');
        $post_id     = $this->getParameter('post_id');
        $user_id     = $this->getUser()->getId();

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository->findOneByNetteUser($this->getUser());
        
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
                
                $postFile = new \App\Model\Entity\FileEntity();
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
            $postOldEntity = $this->postRepository
                ->findOneBy(
                    [
                        'id' => $post_id,
                    ]
                );

            $postOldEntity->category = $categoryEntity;
            $postOldEntity->forum = $forumEntity;
            $postOldEntity->topic = $topicEntity;
            $postOldEntity->user = $userEntity;
            $postOldEntity->title = $values->title;
            $postOldEntity->text = $values->text;
            $postOldEntity->editIpAddress = $this->getHttpRequest()->getRemoteAddress();

            $postHistoryUpdate = new PostHistoryEntity();
            $postHistoryUpdate->post = $postOldEntity;
            $postHistoryUpdate->category = $categoryEntity;
            $postHistoryUpdate->forum = $forumEntity;
            $postHistoryUpdate->topic = $topicEntity;
            $postHistoryUpdate->post = $postOldEntity;
            $postHistoryUpdate->user = $userEntity;
            $postHistoryUpdate->title = $values->title;
            $postHistoryUpdate->text = $values->text;

            $this->em->persist($postOldEntity);
            $this->em->persist($postHistoryUpdate);
            $this->em->flush();
                                          
            //$result = $this->postFacade->update($postNew);
        } else {
            $post = new \App\Model\Entity\PostEntity();
            $post->category = $categoryEntity;
            $post->forum = $forumEntity;
            $post->topic = $topicEntity;
            $post->title = $values->title;
            $post->text = $values->text;
            $post->addIpAddress = $this->getHttpRequest()->getRemoteAddress();

            foreach ($postFiles as $postFile) {
                $post->files->add($postFile);
            }

            $result = $this->postFacade->add($post);

            $emails = $this->topicWatchRepository
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
                $this->bbMailer->setSubject($this->translator->translate('topic_watch_mail_subject'));
                $this->bbMailer->setText(
                    sprintf(
                        $this->translator->translate('topic_watch_mail_text'),
                        $this->link('//Topic:default', $category_id, $forum_id, $topic_id)
                    )
                );
                $this->bbMailer->send();
            }
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
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbHistory(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
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

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
    
    protected function createComponentReportForm(): ReportForm
    {
        return $this->reportForm;
    }
}
