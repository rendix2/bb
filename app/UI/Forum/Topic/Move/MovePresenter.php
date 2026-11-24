<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Move;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicMoveService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;

/**
 * A moderator of the forum of the topic (or the admin) moves the topic to another forum (TopicMoveService); the old forum
 * shows "moved to ..." for a few days.
 */
class MovePresenter extends Presenter
{
    private TopicEntity $topic;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly TopicMoveService $topicMove,
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

        if ($user === null || !$this->topicMove->mayMove($user, $topic)) {
            $this->error('You may not move this topic.', IResponse::S403_Forbidden);
        }

        $this->topic = $topic;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->topic = $this->topic;
    }

    protected function createComponentMoveForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-move.protection');

        $choices = [];

        foreach ($this->topicMove->targets($this->currentUser, $this->topic) as $forum) {
            $choices[$this->postWriteService->categoryOf($forum)?->name ?? ''][$forum->uuid->toString()] = $forum->name;
        }

        // the names of the forums are not translated: no translator for the select, its texts translated here
        $form->addSelect('forum', $this->translator->translate('forum-topic-move.forum.label'), $choices)
            ->setTranslator(null)
            ->setPrompt($this->translator->translate('forum-topic-move.forum.prompt'))
            ->setRequired($this->translator->translate('forum-topic-move.forum.required'));
        $form->addSubmit('send', 'forum-topic-move.send');

        $form->onSuccess[] = $this->moveFormSucceeded(...);

        return $form;
    }

    private function moveFormSucceeded(Form $form) : void
    {
        $uuid = (string) $form->getValues('array')['forum'];
        $target = $this->em->getRepository(ForumEntity::class)->findOneBy(['uuid' => $uuid]);

        try {
            if ($target === null) {
                throw new PostWriteException(TopicMoveService::ERROR_TARGET);
            }

            $this->topicMove->move($this->currentUser, $this->topic, $target);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-topic-move.success', 'success');
        $this->redirect(':Forum:Topic:View:default', ['uuid' => $this->topic->uuid->toString()]);
    }
}
