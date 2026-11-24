<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\FileEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AttachmentService;
use App\Model\Service\UserAvatarService;
use App\Settings\ImagesDir;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;
use Tests\Support\IntegrationTester;

/**
 * The attachments and the avatars in the administration (slice 7e5): the lists and the deletion (the database and the disk).
 * Rolled back after each test; the files written to the disk are deleted.
 */
class AdminFilePagesCest
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
            FileSystem::delete($path);
        }

        FileSystem::delete((string) $I->grabService(ImagesDir::class)->get());
    }

    public function theAdminSeesAndDeletesAnAttachment(IntegrationTester $I) : void
    {
        [$user, $post] = $this->createPost($I);
        $service = $I->grabService(AttachmentService::class);
        $service->store($user, $post, [$this->upload(Image::fromBlank(10, 10, ImageColor::rgb(0, 0, 255))->toString(ImageType::PNG), 'admin-test.png')]);
        $file = $I->em()->getRepository(FileEntity::class)->findOneBy(['post' => $post]) ?? $I->fail('Not stored.');
        $fileId = $file->id;
        $path = $service->path($file);
        $this->written[] = $path;
        $I->assertFileExists($path);

        $I->loginAsAdmin();
        $I->amOnPage('/admin.file.list/');
        $I->see('admin-test.png', '.attachment');
        $I->see($user->username, '.attachment');

        $I->amOnPage('/admin.file.delete/default/' . $fileId);
        $I->submitForm('#frm-deleteForm', []);

        $I->assertNull($I->reload(FileEntity::class, $fileId));
        $I->assertFileDoesNotExist($path);
        $I->assertNotNull($I->reload(PostEntity::class, $post->id));
    }

    public function theAdminSeesAndDeletesAnAvatar(IntegrationTester $I) : void
    {
        $user = $I->createUser('Avatared', role: 'Normal');
        $avatars = $I->grabService(UserAvatarService::class);
        $avatars->change($user, $this->upload(Image::fromBlank(50, 50, ImageColor::rgb(200, 20, 20))->toString(ImageType::PNG), 'avatar.png'));
        $original = (string) $I->grabService(ImagesDir::class)->get() . '/media/' . $user->avatar;
        $I->assertFileExists($original);

        $I->loginAsAdmin();
        $I->amOnPage('/admin.avatar.list/');
        $I->see($user->username, '.avatar');

        $I->amOnPage('/admin.avatar.delete/default/' . $user->id);
        $I->submitForm('#frm-deleteForm', []);

        $I->assertNull($I->reload(UserEntity::class, $user->id)?->avatar);
        $I->assertFileDoesNotExist($original);

        // nothing to delete any more
        $I->seePageIsRefused('/admin.avatar.delete/default/' . $user->id, 404);
    }

    public function theFilesAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.file.list/', 403);
        $I->seePageIsRefused('/admin.avatar.list/', 403);
    }

    private function upload(string $content, string $name) : FileUpload
    {
        $tmp = (string) tempnam(sys_get_temp_dir(), 'admin');
        file_put_contents($tmp, $content);

        return new FileUpload(['name' => $name, 'full_path' => $name, 'size' => strlen($content), 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK]);
    }

    /**
     * @return array{UserEntity, PostEntity}
     */
    private function createPost(IntegrationTester $I) : array
    {
        $em = $I->em();
        $user = $I->createUser('Attacher', role: 'Normal');

        $category = new CategoryEntity();
        $category->name = 'Admin file category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Admin file forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Admin file topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $user;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $user;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post with an attachment';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);
        $em->flush();

        return [$user, $post];
    }
}
