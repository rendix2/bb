<?php declare(strict_types=1);

namespace App\UI\Forum\Post\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\UI\Forum\ForumBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * Deleting a reply, confirmed by a form (POST with CSRF protection, the old code deleted by a GET link).
 */
class DeletePresenter extends ForumBasePresenter
{
    private PostEntity $post;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $post = $this->em->getRepository(PostEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($post === null || !$post->topic->forum->active || !$post->topic->category->active) {
            $this->error('Post was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($user === null || !$this->permissions->isAllowed($user, $post, ForumPrivilege::DELETE) || $this->postWriteService->isFirstPost($post)) {
            $this->error('You may not delete this post.', IResponse::S403_Forbidden);
        }

        $this->post = $post;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->post = $this->post;
    }

    protected function createComponentDeleteForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-post-delete.protection');
        $form->addSubmit('delete', 'forum-post-delete.confirm');

        $form->onSuccess[] = $this->deleteFormSucceeded(...);

        return $form;
    }

    private function deleteFormSucceeded(Form $form) : void
    {
        $topicUuid = $this->post->topic->uuid->toString();

        try {
            $this->postWriteService->deletePost($this->currentUser, $this->post);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->forumFlash('forum-post-delete.success', 'success');
        $this->redirect(':Forum:Topic:View:default', ['uuid' => $topicUuid]);
    }
}
