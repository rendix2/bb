<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\FileEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Settings\PostSetting;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;

/**
 * Attachments of posts (posts.attachments): stored OUTSIDE www/ under a random name with the extension of the type
 * detected from the content - the old code stored them in www/postFiles with the extension of the client (an uploaded
 * .php could be run). Served only by the presenter Forum:Post:Attachment.
 */
class AttachmentService
{
    public const string ERROR_UPLOAD = 'forum-attachment.error.upload';
    public const string ERROR_TYPE = 'forum-attachment.error.type';
    public const string ERROR_SIZE = 'forum-attachment.error.size';
    public const string ERROR_COUNT = 'forum-attachment.error.count';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly PostSetting $postSetting,
    ) {
    }

    /**
     * Checks the uploads before anything is written (the post is not saved when an attachment is wrong).
     *
     * @param list<FileUpload> $uploads
     */
    public function validate(array $uploads) : void
    {
        $settings = $this->settings();

        if (count($uploads) > (int) $settings['maxFiles']) {
            throw new PostWriteException(self::ERROR_COUNT);
        }

        foreach ($uploads as $upload) {
            if (!$upload->isOk()) {
                throw new PostWriteException(self::ERROR_UPLOAD);
            }

            if ($upload->getSize() > (int) $settings['maxSize']) {
                throw new PostWriteException(self::ERROR_SIZE);
            }

            if (!isset($settings['allowedTypes'][(string) $upload->getContentType()])) {
                throw new PostWriteException(self::ERROR_TYPE);
            }
        }
    }

    /**
     * @param list<FileUpload> $uploads already validated
     */
    public function store(UserEntity $user, PostEntity $post, array $uploads) : void
    {
        $this->validate($uploads);
        $settings = $this->settings();

        foreach ($uploads as $upload) {
            $type = (string) $upload->getContentType();
            $file = new FileEntity();
            $file->user = $user;
            $file->post = $post;
            $file->originalName = mb_substr($upload->getSanitizedName(), 0, 255);
            $file->storedName = Random::generate(32) . '.' . $settings['allowedTypes'][$type];
            $file->mimeType = $type;
            $file->size = $upload->getSize();

            $upload->move($this->path($file));
            $this->em->persist($file);
        }

        $this->em->flush();
    }

    public function path(FileEntity $file) : string
    {
        return rtrim((string) $this->settings()['dir'], '/') . '/' . $file->storedName;
    }

    /**
     * @param list<PostEntity> $posts
     * @return list<string> the paths of their attachments (to delete after the posts are deleted)
     */
    public function pathsOf(array $posts) : array
    {
        if ($posts === []) {
            return [];
        }

        $files = $this->em->getRepository(FileEntity::class)->findBy(['post' => $posts]);

        return array_map(fn (FileEntity $file) : string => $this->path($file), $files);
    }

    /**
     * @param list<string> $paths
     */
    public function deleteFiles(array $paths) : void
    {
        foreach ($paths as $path) {
            FileSystem::delete($path);
        }
    }

    /**
     * @param list<PostEntity> $posts
     * @return array<int, list<FileEntity>> post id => its attachments (one query for a page)
     */
    public function attachmentsOf(array $posts) : array
    {
        $byPost = [];

        if ($posts === []) {
            return $byPost;
        }

        foreach ($this->em->getRepository(FileEntity::class)->findBy(['post' => $posts], ['id' => 'ASC']) as $file) {
            $byPost[$file->post->id][] = $file;
        }

        return $byPost;
    }

    /**
     * @return array{dir: string, maxSize: int, maxFiles: int, allowedTypes: array<string, string>}
     */
    private function settings() : array
    {
        return $this->postSetting->get()['attachments'];
    }
}
