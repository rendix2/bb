<?php declare(strict_types=1);

namespace App\UI\Forum\Pm\Create;

use App\Model\Service\PrivateMessageException;
use App\UI\Forum\Pm\Accessory\PrivateMessagePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Form;

/**
 * A new thread: recipients by username (separated by commas), subject and the first message (Markdown); ?to=<username> fills
 * the recipient (the link on a profile).
 */
class CreatePresenter extends PrivateMessagePresenter
{
    public function actionDefault(?string $to = null) : void
    {
        $this->loadViewer();

        if ($to !== null && !$this['createForm']->isSubmitted()) {
            $this['createForm']->setDefaults(['recipients' => $to]);
        }
    }

    public function renderDefault() : void
    {
        $this->template->preview ??= null;
    }

    protected function createComponentCreateForm() : BootstrapForm
    {
        $form = $this->newForm();

        $form->addText('recipients', 'forum-pm-create.form.recipients.label')
            ->setRequired('forum-pm-create.form.recipients.required')
            ->setOption('description', $this->translator->translate('forum-pm-create.form.recipients.description'));

        $form->addText('subject', 'forum-pm-create.form.subject.label')
            ->setRequired('forum-pm-create.form.subject.required')
            ->setMaxLength(1024);

        $form->addTextArea('text', 'forum-pm-create.form.text.label', rows: 10)
            ->setRequired('forum-pm-create.form.text.required');

        $form->addSubmit('send', 'forum-pm-create.form.send');
        $form->addSubmit('preview', 'forum-pm.form.preview')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary');

        $form->onSuccess[] = $this->createFormSucceeded(...);

        return $form;
    }

    private function createFormSucceeded(Form $form) : void
    {
        if ($form['preview'] instanceof \Nette\Forms\Controls\SubmitButton && $form['preview']->isSubmittedBy()) {
            $this->template->preview = (string) $form->getUntrustedValues()->text;

            return;
        }

        $values = $form->getValues(CreateThreadValues::class);

        try {
            $thread = $this->privateMessages->createThread(
                $this->viewer,
                $values->subject,
                $this->privateMessages->parseUsernames($values->recipients),
                $values->text,
            );
        } catch (PrivateMessageException $exception) {
            $this->formError($form, $exception);

            return;
        }

        $this->flashMessage('forum-pm-create.success', 'success');
        $this->redirect(':Forum:Pm:Thread:default', ['uuid' => $thread->uuid->toString()]);
    }
}
