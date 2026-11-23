<?php declare(strict_types=1);

namespace Tests\Functional;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tests\Support\FunctionalTester;

/**
 * The avatar over HTTP (slice 4e): uploaded on Web:User:Edit (POST, CSRF token of the form, multipart), shown on the profile,
 * served by Web:Image as a WebP never sniffed, removed again with its files. The web server writes to data/images of the
 * application: the files of the test are deleted after it.
 */
class UserAvatarCest
{
    /** @var list<string> the names of the avatars written by the test */
    private array $names = [];

    public function _after(FunctionalTester $I) : void
    {
        $dir = codecept_root_dir('data/images');

        foreach ($this->names as $name) {
            if (!is_dir($dir)) {
                break;
            }

            foreach (Finder::findFiles($name)->from($dir) as $file) {
                FileSystem::delete($file->getPathname());
            }
        }

        $this->names = [];
    }

    public function anAvatarIsUploadedShownServedAndRemoved(FunctionalTester $I) : void
    {
        $user = $I->createUser('avatar');
        $I->login($user->username, FunctionalTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->seePageIsOk('edit');
        $I->dontSeeElement('#frm-avatarDeleteForm');
        $I->attachFile('#frm-avatarForm input[name=avatar]', 'attachments/pixel.png');
        $I->click('#frm-avatarForm [name=send]');
        $I->seePageIsOk('upload');

        $avatar = $this->avatarOf($I, $user->id);
        $I->assertNotNull($avatar);
        $name = basename($avatar);
        $this->names[] = $name;
        $I->assertFileExists(codecept_root_dir('data/images/cache/avatars/_avatar48/' . $name));

        $I->amOnPage('/web.user.view/');
        $I->seePageIsOk('profile');
        $src = $I->grabAttributeFrom('img.profile-avatar', 'src');
        $I->assertStringEndsWith('/cache/avatars/_avatar128/' . $name, $src);

        // public: fetched without the session of the user (the avatars are shown with the posts of a public topic)
        $headers = array_change_key_case((array) get_headers($src, true));
        $I->assertStringContainsString(' 200 ', (string) ($headers[0] ?? ''));
        $I->assertSame('image/webp', $headers['content-type'] ?? null);
        $I->assertSame('nosniff', $headers['x-content-type-options'] ?? null);

        $I->amOnPage('/web.user.edit/');
        $I->click('#frm-avatarDeleteForm [name=delete]');
        $I->seePageIsOk('delete');
        $I->assertNull($this->avatarOf($I, $user->id));
        $I->assertFileDoesNotExist(codecept_root_dir('data/images/cache/avatars/_avatar128/' . $name));
        $I->assertFileDoesNotExist(codecept_root_dir('data/images/media/avatars/' . $name));

        $I->amOnPage('/cache/avatars/_avatar128/' . $name);
        $I->seeRefused(404, 'the removed avatar');
    }

    private function avatarOf(FunctionalTester $I, int $id) : ?string
    {
        $em = $I->grabService(EntityManagerDecorator::class);
        $em->clear();

        return $em->find(UserEntity::class, $id)?->avatar;
    }
}
