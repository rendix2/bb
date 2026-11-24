<?php declare(strict_types=1);

namespace App\UI\Forum\Pm\Thread;

use App\Model\Entity\PrivateMessageParticipantEntity;
use App\Model\Entity\PrivateMessageThreadEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PrivateMessageException;
use App\UI\Forum\Pm\Accessory\PrivateMessagePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * One thread: only for its active participants (404 for anyone else, also for one who left). Opening it marks it read (and the
 * next message may mail him again). Changes are POST forms with a CSRF token; the rights are checked again in the service.
 */
class ThreadPresenter extends PrivateMessagePresenter
{
    private PrivateMessageThreadEntity $thread;

    private PrivateMessageParticipantEntity $participant;

    public function actionDefault(string $uuid) : void
    {
        $this->loadViewer();

        $thread = $this->em->getRepository(PrivateMessageThreadEntity::class)->findOneBy(['uuid' => $uuid]);
        $participant = $thread !== null ? $this->privateMessages->participantOf($this->viewer, $thread) : null;

        if ($thread === null || $participant === null) {
            $this->error('Thread was not found.');
        }

        $this->thread = $thread;
        $this->participant = $participant;

        // not when a form is sent: "mark unread" must not be undone by the same request
        if ($this->getSignal() === null) {
            $this->privateMessages->visited($this->viewer, $thread);
        }
    }

    public function renderDefault() : void
    {
        $this->template->preview ??= null;
        $this->template->thread = $this->thread;
        $this->template->participant = $this->participant;
        $this->template->messages = $this->privateMessages->messagesOf($this->thread);
        $this->template->participants = $this->privateMessages->activeParticipants($this->thread);
        $this->template->isFounder = $this->thread->user?->id === $this->viewer->id;
    }

    protected function createComponentReplyForm() : BootstrapForm
    {
        $form = $this->newForm();

        $form->addTextArea('text', 'forum-pm-thread.form.text.label', rows: 8)
            ->setRequired('forum-pm-thread.form.text.required');

        $form->addSubmit('send', 'forum-pm-thread.form.send');
        $form->addSubmit('preview', 'forum-pm.form.preview')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary');

        $form->onSuccess[] = function (Form $form) : void {
            if ($form['preview'] instanceof SubmitButton && $form['preview']->isSubmittedBy()) {
                $this->template->preview = (string) $form->getUntrustedValues()->text;

                return;
            }

            try {
                $message = $this->privateMessages->reply($this->viewer, $this->thread, (string) $form->getValues('array')['text']);
            } catch (PrivateMessageException $exception) {
                $this->formError($form, $exception);

                return;
            }

            $this->forumFlash('forum-pm-thread.replied', 'success');
            $this->redirect('this#message-' . $message->id);
        };

        return $form;
    }

    /**
     * Only the founder: more participants (usernames separated by commas).
     */
    protected function createComponentAddParticipantsForm() : BootstrapForm
    {
        $form = $this->newForm();

        $form->addText('usernames', 'forum-pm-thread.form.usernames.label')
            ->setRequired('forum-pm-thread.form.usernames.required');
        $form->addSubmit('add', 'forum-pm-thread.form.add')
            ->setHtmlAttribute('class', 'btn btn-outline-primary btn-sm');

        $form->onSuccess[] = function (Form $form) : void {
            try {
                $this->privateMessages->addParticipants($this->viewer, $this->thread, $this->privateMessages->parseUsernames((string) $form->getValues('array')['usernames']));
            } catch (PrivateMessageException $exception) {
                $this->formError($form, $exception);

                return;
            }

            $this->forumFlash('forum-pm-thread.added', 'success');
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentStarForm() : BootstrapForm
    {
        $starred = $this->participant->isStarred;

        return $this->toggle(
            $starred ? 'forum-pm-thread.unstar' : 'forum-pm-thread.star',
            fn (UserEntity $user) => $this->privateMessages->setStarred($user, $this->thread, !$starred),
            $starred ? 'forum-pm-thread.unstarred' : 'forum-pm-thread.starred',
        );
    }

    protected function createComponentMailForm() : BootstrapForm
    {
        $wantsMail = $this->participant->wantsMail;

        return $this->toggle(
            $wantsMail ? 'forum-pm-thread.mailOff' : 'forum-pm-thread.mailOn',
            fn (UserEntity $user) => $this->privateMessages->setWantsMail($user, $this->thread, !$wantsMail),
            $wantsMail ? 'forum-pm-thread.mailTurnedOff' : 'forum-pm-thread.mailTurnedOn',
        );
    }

    /**
     * Unread again: back to the list (the thread page would mark it read).
     */
    protected function createComponentUnreadForm() : BootstrapForm
    {
        return $this->toggle(
            'forum-pm-thread.markUnread',
            fn (UserEntity $user) => $this->privateMessages->setRead($user, $this->thread, false),
            'forum-pm-thread.markedUnread',
            ':Forum:Pm:List:default',
        );
    }

    protected function createComponentLeaveForm() : BootstrapForm
    {
        $form = $this->toggle(
            'forum-pm-thread.leave',
            fn (UserEntity $user) => $this->privateMessages->leave($user, $this->thread),
            'forum-pm-thread.left',
            ':Forum:Pm:List:default',
        );
        $send = $form['send'];

        if ($send instanceof SubmitButton) {
            $send->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm')
                ->setHtmlAttribute('onclick', 'return confirm(' . json_encode($this->translator->translate('forum-pm-thread.leaveConfirm')) . ')');
        }

        return $form;
    }

    /**
     * @param callable(UserEntity): void $action
     */
    private function toggle(string $caption, callable $action, string $done, string $redirect = 'this') : BootstrapForm
    {
        $form = $this->buttonForm($caption);
        $form->onSuccess[] = function () use ($action, $done, $redirect) : void {
            try {
                $action($this->viewer);
                $this->forumFlash($done, 'success');
            } catch (PrivateMessageException $exception) {
                $this->forumFlash($exception->getMessage(), 'danger', $exception->parameters);
            }

            $this->redirect($redirect);
        };

        return $form;
    }
}
