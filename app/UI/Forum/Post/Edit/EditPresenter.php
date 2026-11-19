<?php declare(strict_types=1);

namespace App\UI\Forum\Post\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicPaginationService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * Editing a post (the author by the flags of the forum, a moderator, the admin); the previous version goes to the history.
 */
class EditPresenter extends Presenter
{
    private PostEntity $post;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostWriteService $postWriteService,
        private readonly TopicPaginationService $topicPagination,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $post = $this->em->getRepository(PostEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($post === null || !$post->forum->active || !$post->category->active) {
            $this->error('Post was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($user === null || !$this->permissions->isAllowed($user, $post, ForumPrivilege::EDIT)) {
            $this->error('You may not edit this post.', IResponse::S403_Forbidden);
        }

        $this->post = $post;
        $this->currentUser = $user;

        $this['editPostForm']->setDefaults(['title' => $post->title ?? '', 'text' => $post->text]);
    }

    public function renderDefault() : void
    {
        $this->template->preview ??= null;
        $this->template->post = $this->post;
    }

    protected function createComponentEditPostForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-post-edit.form.protection');

        $form->addText('title', 'forum-post-edit.form.title.label')
            ->setMaxLength(1024);

        $form->addTextArea('text', 'forum-post-edit.form.text.label', rows: 12)
            ->setRequired('forum-post-edit.form.text.required');

        $form->addSubmit('send', 'forum-post-edit.form.send');
        // shows the text as it will look (Markdown), saves nothing
        $form->addSubmit('preview', 'forum-post-edit.form.preview')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary');

        $form->onSuccess[] = $this->editPostFormSucceeded(...);

        return $form;
    }

    private function editPostFormSucceeded(Form $form) : void
    {
        if ($form['preview'] instanceof \Nette\Forms\Controls\SubmitButton && $form['preview']->isSubmittedBy()) {
            $this->template->preview = (string) $form->getUntrustedValues()->text;

            return;
        }

        $values = $form->getValues(EditPostValues::class);

        try {
            $this->postWriteService->edit($this->currentUser, $this->post, $values->title, $values->text, (string) $this->getHttpRequest()->getRemoteAddress());
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-post-edit.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default#post-' . $this->post->id, ['uuid' => $this->post->topic->uuid->toString(), 'page' => $this->topicPagination->pageOf($this->post)]);
    }
}
