<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Poll;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PollService;
use App\Model\Service\PostWriteException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;
use Nette\Utils\Strings;

/**
 * Adding the poll of a topic (its author while the topic is open, or a moderator).
 */
class PollPresenter extends Presenter
{
    private TopicEntity $topic;

    private UserEntity $currentUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly PollService $pollService,
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

        if ($user === null || !$this->pollService->mayAddPoll($user, $topic) || $this->pollService->pollOf($topic) !== null) {
            $this->error('You may not add a poll to this topic.', IResponse::S403_Forbidden);
        }

        $this->topic = $topic;
        $this->currentUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->topic = $this->topic;
    }

    protected function createComponentAddPollForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-topic-poll.form.protection');

        $form->addText('question', 'forum-topic-poll.form.question.label')
            ->setRequired('forum-topic-poll.form.question.required')
            ->setMaxLength(1024);

        $form->addTextArea('answers', 'forum-topic-poll.form.answers.label', rows: 6)
            ->setRequired('forum-topic-poll.form.answers.required');

        $form->addDateTime('endsAt', 'forum-topic-poll.form.endsAt.label');

        $form->addSubmit('send', 'forum-topic-poll.form.send');

        $form->onSuccess[] = $this->addPollFormSucceeded(...);

        return $form;
    }

    private function addPollFormSucceeded(Form $form) : void
    {
        $values = $form->getValues(AddPollValues::class);

        try {
            $this->pollService->addPoll($this->currentUser, $this->topic, $values->question, Strings::split(Strings::unixNewLines($values->answers), '~\n~'), $values->endsAt);
        } catch (PostWriteException $exception) {
            $form->addError($exception->getMessage());

            return;
        }

        $this->flashMessage('forum-topic-poll.form.success', 'success');
        $this->redirect(':Forum:Topic:View:default', ['uuid' => $this->topic->uuid->toString()]);
    }
}
