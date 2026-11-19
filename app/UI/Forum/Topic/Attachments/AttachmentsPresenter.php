<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Attachments;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Service\AttachmentService;
use Nette\Application\UI\Presenter;

/**
 * All attachments of a topic with links to download them (Forum:Post:Attachment).
 */
class AttachmentsPresenter extends Presenter
{
    private TopicEntity $topic;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly AttachmentService $attachmentService,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $topic = $this->em->getRepository(TopicEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($topic === null || !$topic->forum->active || !$topic->category->active) {
            $this->error('Topic was not found.');
        }

        $this->topic = $topic;
    }

    public function renderDefault() : void
    {
        $this->template->topic = $this->topic;
        $this->template->files = $this->attachmentService->attachmentsOfTopic($this->topic);
    }
}
