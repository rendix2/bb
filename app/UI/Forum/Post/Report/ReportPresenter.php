<?php declare(strict_types=1);

namespace App\UI\Forum\Post\Report;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\ReportService;
use App\Model\Service\TopicPaginationService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * Reporting a post to the moderators (a logged-in user who sees the post, not his own, once per post). The moderators
 * handle the reports in slice 6.
 */
class ReportPresenter extends Presenter
{
    private PostEntity $post;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ReportService $reportService,
        private readonly TopicPaginationService $topicPagination,
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

        if ($user === null || !$this->reportService->mayReport($user, $post)) {
            $this->error('You may not report this post.', IResponse::S403_Forbidden);
        }

        $this->post = $post;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->post = $this->post;
        $this->template->alreadyReported = $this->reportService->hasReported($this->currentUser, $this->post);
    }

    protected function createComponentReportForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-post-report.protection');

        $form->addTextArea('text', 'forum-post-report.text.label', rows: 5)
            ->setRequired('forum-post-report.text.required')
            ->setMaxLength(ReportService::TEXT_MAX_LENGTH);

        $form->addSubmit('send', 'forum-post-report.send');

        $form->onSuccess[] = $this->reportFormSucceeded(...);

        return $form;
    }

    private function reportFormSucceeded(Form $form) : void
    {
        $values = $form->getValues(ReportValues::class);

        try {
            $this->reportService->report($this->currentUser, $this->post, $values->text, (string) $this->getHttpRequest()->getRemoteAddress());
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-post-report.success', 'success');
        $this->redirect(':Forum:Topic:View:default#post-' . $this->post->id, ['uuid' => $this->post->topic->uuid->toString(), 'page' => $this->topicPagination->pageOf($this->post)]);
    }
}
