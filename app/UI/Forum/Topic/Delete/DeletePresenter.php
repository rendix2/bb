<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
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
 * Deleting a topic with all its posts, confirmed by a form (POST with CSRF protection).
 */
class DeletePresenter extends ForumBasePresenter
{
    private TopicEntity $topic;

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
        $topic = $this->em->getRepository(TopicEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($topic === null || !$topic->forum->active || !$topic->category->active) {
            $this->error('Topic was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($user === null || !$this->permissions->isAllowed($user, $topic, ForumPrivilege::DELETE)) {
            $this->error('You may not delete this topic.', IResponse::S403_Forbidden);
        }

        $this->topic = $topic;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->topic = $this->topic;
    }

    protected function createComponentDeleteForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-delete.protection');
        $form->addSubmit('delete', 'forum-topic-delete.confirm');

        $form->onSuccess[] = $this->deleteFormSucceeded(...);

        return $form;
    }

    private function deleteFormSucceeded(Form $form) : void
    {
        $forumUuid = $this->topic->forum->uuid->toString();

        try {
            $this->postWriteService->deleteTopic($this->currentUser, $this->topic);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->forumFlash('forum-topic-delete.success', 'success');
        $this->redirect(':Forum:Forum:View:default', ['uuid' => $forumUuid]);
    }
}
