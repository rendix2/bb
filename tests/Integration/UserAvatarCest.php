<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Facade\UserFacade;
use App\Model\Service\AvatarException;
use App\Model\Service\UserAvatarService;
use App\Settings\Avatars;
use App\Settings\ImageDir;
use App\Settings\ImagesDir;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;
use ReflectionProperty;
use Tests\Support\IntegrationTester;

/**
 * Avatars (slice 4e, contributte/imagist): only static images judged by their content, re-encoded to a 256x256 WebP (no EXIF,
 * no data appended after the image, no animation), thumbnails 128/48 made on upload, outside www/, served by Web:Image only
 * for existing users, deleted from the disk with the avatar or the user. The test images are made by GD here (nothing real).
 * Rolled back after each test; the images of the tests (parameter imagesDir = temp of the tests) are deleted.
 */
class UserAvatarCest
{
    /** @var array<string, mixed>|null */
    private ?array $avatarsBefore = null;

    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        if ($this->avatarsBefore !== null) {
            (new ReflectionProperty(ImageDir::class, 'dir'))->setValue($I->grabService(Avatars::class), $this->avatarsBefore);
            $this->avatarsBefore = null;
        }

        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
        FileSystem::delete($this->imagesDir($I));
    }

    /**
     * Regression: the old code kept the upload as it came (www/avatars, the extension of the client) - with EXIF (GPS).
     */
    public function anImageIsReencodedWithoutExifAndGetsItsThumbnails(IntegrationTester $I) : void
    {
        $user = $I->createUser('Avatar', role: 'Normal');

        $this->service($I)->change($user, $this->upload($this->jpegWithExif()));

        $saved = $I->reload(UserEntity::class, $user->id);
        $I->assertNotNull($saved?->avatar);
        $I->assertMatchesRegularExpression('#^avatars/[a-zA-Z0-9]{32}\.webp$#', (string) $saved->avatar);

        foreach ($this->paths($I, (string) $saved->avatar) as $size => $path) {
            $I->assertFileExists($path);
            $content = (string) file_get_contents($path);
            $I->assertStringNotContainsString('GPS-SECRET', $content, $path);
            $I->assertSame(ImageType::WEBP, Image::detectTypeFromFile($path, $width, $height));
            $I->assertSame([$size, $size], [$width, $height]);
        }

        // outside the public directory www/
        $wwwDir = (string) realpath((string) $I->grabService(\Nette\DI\Container::class)->getParameters()['wwwDir']);
        $I->assertStringStartsNotWith($wwwDir . '/', (string) realpath($this->paths($I, (string) $saved->avatar)[256]));
    }

    /**
     * Regression: a valid PNG with PHP code after IEND was stored as uploaded.
     */
    public function theCodeAppendedToAnImageIsDropped(IntegrationTester $I) : void
    {
        $user = $I->createUser('Polyglot', role: 'Normal');

        $this->service($I)->change($user, $this->upload($this->png(10, 10) . '<?php echo "polyglot"; ?>'));

        $avatar = (string) $I->reload(UserEntity::class, $user->id)?->avatar;

        foreach ($this->paths($I, $avatar) as $path) {
            $I->assertStringNotContainsString('<?php', (string) file_get_contents($path));
        }
    }

    /**
     * Regression: the type was taken from the name of the client; a PHP file named .png is no image.
     */
    public function notAnImageIsRefused(IntegrationTester $I) : void
    {
        $user = $I->createUser('Evil', role: 'Normal');

        $this->expectError($I, UserAvatarService::ERROR_TYPE, $user, (string) file_get_contents(codecept_data_dir('attachments/evil.png')));
        $this->expectError($I, UserAvatarService::ERROR_TYPE, $user, 'BM' . str_repeat("\0", 64));
        $I->assertDirectoryDoesNotExist($this->imagesDir($I) . '/media/avatars');
    }

    /**
     * Decision of the user: no animated GIF avatars (with or without the looping extension); a static GIF is fine.
     */
    public function anAnimatedGifIsRefused(IntegrationTester $I) : void
    {
        $user = $I->createUser('Animated', role: 'Normal');

        $this->expectError($I, UserAvatarService::ERROR_ANIMATED, $user, $this->animatedGif(true));
        $this->expectError($I, UserAvatarService::ERROR_ANIMATED, $user, $this->animatedGif(false));
        $user = $I->reload(UserEntity::class, $user->id);
        $I->assertNotNull($user);
        $I->assertNull($user->avatar);

        $this->service($I)->change($user, $this->upload(Image::fromBlank(20, 20, ImageColor::rgb(0, 0, 255))->toString(ImageType::GIF)));
        $I->assertNotNull($I->reload(UserEntity::class, $user->id)?->avatar);
    }

    public function theLimitsOfTheConfigurationAreKept(IntegrationTester $I) : void
    {
        $user = $I->createUser('Large', role: 'Normal');
        $avatars = $I->grabService(Avatars::class);

        $this->expectError($I, UserAvatarService::ERROR_DIMENSIONS, $user, $this->png($avatars->getMaxWidth() + 1, 1));

        $this->avatarsBefore = (new ReflectionProperty(ImageDir::class, 'dir'))->getValue($avatars);
        (new ReflectionProperty(ImageDir::class, 'dir'))->setValue($avatars, ['maxFileSize' => 10] + $this->avatarsBefore);
        $this->expectError($I, UserAvatarService::ERROR_SIZE, $user, $this->png(10, 10));
    }

    public function aNewAvatarAndTheDeletionRemoveTheOldFiles(IntegrationTester $I) : void
    {
        $user = $I->createUser('Replace', role: 'Normal');
        $service = $this->service($I);

        $service->change($user, $this->upload($this->png(300, 300)));
        $first = (string) $user->avatar;
        $service->change($user, $this->upload($this->png(64, 64)));
        $second = (string) $user->avatar;

        $I->assertNotSame($first, $second);

        foreach ($this->paths($I, $first) as $path) {
            $I->assertFileDoesNotExist($path);
        }

        $service->delete($user);
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->avatar);

        foreach ($this->paths($I, $second) as $path) {
            $I->assertFileDoesNotExist($path);
        }
    }

    public function deletingTheUserDeletesTheFiles(IntegrationTester $I) : void
    {
        $user = $I->createUser('Deleted', role: 'Normal');
        $this->service($I)->change($user, $this->upload($this->png(50, 50)));
        $avatar = (string) $user->avatar;
        $I->assertFileExists($this->paths($I, $avatar)[48]);

        $I->grabService(UserFacade::class)->delete($user);

        foreach ($this->paths($I, $avatar) as $path) {
            $I->assertFileDoesNotExist($path);
        }
    }

    /**
     * The profile shows the thumbnail 128, a public topic the thumbnail 48 even to a guest; Web:Image sends only thumbnails
     * of existing users, never sniffed.
     */
    public function theAvatarIsShownAndServed(IntegrationTester $I) : void
    {
        $user = $I->createUser('Shown', role: 'Normal');
        $this->service($I)->change($user, $this->upload($this->png(50, 50)));
        $name = basename((string) $user->avatar);
        $topic = $this->createTopic($I, $user);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeResponseCodeIs(200);
        $I->seeInSource('/cache/avatars/_avatar48/' . $name . '"');

        $I->amOnPage('/cache/avatars/_avatar48/' . $name);
        $I->seeResponseCodeIs(200);
        $I->assertSame('nosniff', $I->grabService(IResponse::class)->getHeader('X-Content-Type-Options'));

        $I->login($user->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/');
        $I->seeInSource('/cache/avatars/_avatar128/' . $name . '"');
        $I->amOnPage('/web.user.edit/');
        $I->seeElement('#frm-avatarForm');
        $I->seeElement('#frm-avatarDeleteForm');

        // the files of an avatar no user has (any more) are not sent
        $user->avatar = null;
        $I->em()->flush();
        $I->seePageIsRefused('/cache/avatars/_avatar48/' . $name, 404);
        $I->seePageIsRefused('/cache/avatars/_avatar48/' . str_repeat('a', 32) . '.webp', 404);
    }

    /**
     * The removal is a POST form with a CSRF token (the old code deleted the avatar by a GET link).
     */
    public function theAvatarIsNotRemovedWithoutTheToken(IntegrationTester $I) : void
    {
        $user = $I->createUser('Token', role: 'Normal');
        $this->service($I)->change($user, $this->upload($this->png(50, 50)));
        $avatar = $user->avatar;
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-avatarDeleteForm', ['_token_' => 'forged']);
        $I->assertSame($avatar, $I->reload(UserEntity::class, $user->id)?->avatar);
        $I->assertFileExists($this->paths($I, (string) $avatar)[48]);

        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-avatarDeleteForm', []);
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->avatar);
        $I->assertFileDoesNotExist($this->paths($I, (string) $avatar)[48]);
    }

    private function service(IntegrationTester $I) : UserAvatarService
    {
        return $I->grabService(UserAvatarService::class);
    }

    private function imagesDir(IntegrationTester $I) : string
    {
        return (string) $I->grabService(ImagesDir::class)->get();
    }

    /**
     * @return array<int, string> size => path (256 the stored original)
     */
    private function paths(IntegrationTester $I, string $avatar) : array
    {
        $dir = $this->imagesDir($I);
        $name = basename($avatar);

        return [
            256 => $dir . '/media/avatars/' . $name,
            128 => $dir . '/cache/avatars/_avatar128/' . $name,
            48 => $dir . '/cache/avatars/_avatar48/' . $name,
        ];
    }

    private function expectError(IntegrationTester $I, string $message, UserEntity $user, string $content) : void
    {
        try {
            $this->service($I)->change($user, $this->upload($content));
        } catch (AvatarException $exception) {
            $I->assertSame($message, $exception->getMessage());

            return;
        }

        $I->fail('Expected the error ' . $message . '.');
    }

    private function upload(string $content) : FileUpload
    {
        $tmp = (string) tempnam(sys_get_temp_dir(), 'avatar');
        file_put_contents($tmp, $content);

        return new FileUpload(['name' => 'avatar.png', 'full_path' => 'avatar.png', 'size' => strlen($content), 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK]);
    }

    private function png(int $width, int $height) : string
    {
        return Image::fromBlank($width, $height, ImageColor::rgb(200, 20, 20))->toString(ImageType::PNG);
    }

    /**
     * A JPEG with an APP1 (EXIF) segment carrying a marker, right after SOI.
     */
    private function jpegWithExif() : string
    {
        $jpeg = Image::fromBlank(300, 200, ImageColor::rgb(20, 200, 20))->toString(ImageType::JPEG);
        $exif = "Exif\0\0GPS-SECRET-50.0755N-14.4378E";

        return substr($jpeg, 0, 2) . "\xFF\xE1" . pack('n', strlen($exif) + 2) . $exif . substr($jpeg, 2);
    }

    /**
     * Two frames of a GIF made by GD, optionally with the looping extension NETSCAPE2.0.
     */
    private function animatedGif(bool $loop) : string
    {
        $gif = Image::fromBlank(10, 10, ImageColor::rgb(255, 0, 0))->toString(ImageType::GIF);
        $packed = ord($gif[10]);
        $header = 13 + (($packed & 0x80) !== 0 ? 3 * (2 << ($packed & 0x07)) : 0);
        // the image (descriptor + data) without the trailer, with a graphic control extension before each frame
        $frame = substr($gif, $header, -1);
        $frame = str_starts_with($frame, "\x21\xF9") ? $frame : "\x21\xF9\x04\x00\x0A\x00\x00\x00" . $frame;
        $netscape = $loop ? "\x21\xFF\x0BNETSCAPE2.0\x03\x01\x00\x00\x00" : '';

        return 'GIF89a' . substr($gif, 6, $header - 6) . $netscape . $frame . $frame . ';';
    }

    private function createTopic(IntegrationTester $I, UserEntity $user) : TopicEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Avatar category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Avatar forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Avatar topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $user;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $user;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post with an avatar';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);
        $em->flush();

        return $topic;
    }
}
