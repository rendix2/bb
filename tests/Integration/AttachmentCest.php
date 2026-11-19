<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\FileEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AttachmentService;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use Nette\Http\FileUpload;
use Tests\Support\IntegrationTester;

/**
 * Attachments of posts (slice 3l): stored outside www/ under a random name with the extension of the DETECTED type,
 * served by Forum:Post:Attachment, deleted from the disk with their post. Rolled back after each test; the files written
 * to the disk are deleted.
 */
class AttachmentCest
{
    /** @var list<string> */
    private array $written = [];

    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
        $this->written = [];
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();

        foreach ($this->written as $path) {
            @unlink($path);
        }
    }

    public function anImageIsStoredOutsideWwwAndServed(IntegrationTester $I) : void
    {
        [$user, $post] = $this->createPost($I);
        $service = $I->grabService(AttachmentService::class);

        $service->store($user, $post, [$this->upload('pixel.png', 'photo.png')]);

        $file = $I->em()->getRepository(FileEntity::class)->findOneBy(['post' => $post]);
        $I->assertNotNull($file);
        $path = $service->path($file);
        $this->written[] = $path;
        $I->assertFileExists($path);
        // not under the public directory www/ of the application
        $wwwDir = (string) realpath((string) $I->grabService(\Nette\DI\Container::class)->getParameters()['wwwDir']);
        $I->assertStringStartsNotWith($wwwDir . '/', (string) realpath($path));
        $I->assertSame('image/png', $file->mimeType);
        $I->assertStringEndsWith('.png', $file->storedName);
        $I->assertSame('photo.png', $file->originalName);

        $I->amOnPage('/forum.post.attachment/' . $file->uuid->toString());
        $I->seeResponseCodeIs(200);
    }

    /**
     * Regression: the old code kept the extension of the client in www/postFiles, an uploaded PHP file could be run.
     */
    public function aFileIsJudgedByItsContentNotByItsName(IntegrationTester $I) : void
    {
        [$user, $post] = $this->createPost($I);
        $service = $I->grabService(AttachmentService::class);

        $this->expectError($I, AttachmentService::ERROR_TYPE, fn () => $service->store($user, $post, [$this->upload('evil.png', 'innocent.png')]));
        $I->assertSame(0, $I->em()->getRepository(FileEntity::class)->count(['post' => $post]));
    }

    public function tooManyAttachmentsAreRefused(IntegrationTester $I) : void
    {
        $service = $I->grabService(AttachmentService::class);
        // posts.attachments.maxFiles = 5
        $uploads = array_map(fn (int $i) => $this->upload('pixel.png', "p$i.png"), range(1, 6));

        $this->expectError($I, AttachmentService::ERROR_COUNT, fn () => $service->validate($uploads));
    }

    public function deletingThePostDeletesTheFileFromTheDisk(IntegrationTester $I) : void
    {
        [$user, $post, $topic] = $this->createPost($I);
        $service = $I->grabService(AttachmentService::class);
        $service->store($user, $post, [$this->upload('pixel.png', 'photo.png')]);
        $file = $I->em()->getRepository(FileEntity::class)->findOneBy(['post' => $post]);
        $I->assertNotNull($file);
        $path = $service->path($file);
        $this->written[] = $path;

        // the post is a reply (the first post goes only with the topic), its author may delete it
        $I->grabService(PostWriteService::class)->deletePost($user, $post);

        $I->assertFileDoesNotExist($path);
        $I->assertNotNull($topic);
    }

    private function upload(string $fixture, string $name) : FileUpload
    {
        // a copy: FileUpload::move() takes the temporary file away
        $tmp = tempnam(sys_get_temp_dir(), 'att');
        copy(codecept_data_dir('attachments/' . $fixture), (string) $tmp);

        return new FileUpload(['name' => $name, 'full_path' => $name, 'size' => filesize((string) $tmp), 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK]);
    }

    private function expectError(IntegrationTester $I, string $message, callable $action) : void
    {
        try {
            $action();
        } catch (PostWriteException $exception) {
            $I->assertSame($message, $exception->getMessage());

            return;
        }

        $I->fail('Expected the error ' . $message . '.');
    }

    /**
     * @return array{UserEntity, PostEntity, TopicEntity} a user, his reply (not the first post) and the topic
     */
    private function createPost(IntegrationTester $I) : array
    {
        $em = $I->em();
        $user = $I->createUser('Attacher', role: 'Normal');

        $category = new CategoryEntity();
        $category->name = 'Attachment category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Attachment forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Attachment topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $user;
        $em->persist($topic);

        $posts = [];

        foreach (['The first post', 'A reply with an attachment'] as $text) {
            $post = new PostEntity();
            $post->user = $user;
            $post->category = $category;
            $post->forum = $forum;
            $post->topic = $topic;
            $post->title = null;
            $post->text = $text;
            $post->addIpAddress = '127.0.0.1';
            $post->editIpAddress = null;
            $em->persist($post);
            $em->flush();
            $posts[] = $post;
        }

        return [$user, $posts[1], $topic];
    }

}
