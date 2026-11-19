<?php declare(strict_types=1);

namespace App\UI\Forum\Post\Attachment;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\FileEntity;
use App\Model\Service\AttachmentService;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;

/**
 * Sends an attachment of a post (the files are outside www/): only of an active forum, with the type detected on upload;
 * images inline, other files as a download, never sniffed by the browser.
 */
class AttachmentPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly AttachmentService $attachmentService,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $file = $this->em->getRepository(FileEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($file === null || !$file->post->forum->active || !$file->post->category->active) {
            $this->error('Attachment was not found.');
        }

        $path = $this->attachmentService->path($file);

        if (!is_file($path)) {
            $this->error('Attachment was not found.');
        }

        $this->getHttpResponse()->setHeader('X-Content-Type-Options', 'nosniff');
        $this->sendResponse(new FileResponse($path, $file->originalName, $file->mimeType, forceDownload: !str_starts_with($file->mimeType, 'image/')));
    }
}
