<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\RankAdminService;
use App\Model\Service\RankImageService;
use App\Settings\ImagesDir;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;
use Tests\Support\IntegrationTester;

/**
 * The administration of ranks (slice 7d). Rolled back after each test; the images of the tests (parameter imagesDir = temp of
 * the tests) are deleted.
 */
class AdminRankPagesCest
{
    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
        FileSystem::delete((string) $I->grabService(ImagesDir::class)->get() . '/' . RankImageService::SCOPE);
        FileSystem::delete((string) $I->grabService(ImagesDir::class)->get() . '/cache/' . RankImageService::SCOPE);
    }

    public function theAdminCreatesARankByPostsAndASpecialOne(IntegrationTester $I) : void
    {
        $I->loginAsAdmin();

        $I->amOnPage('/admin.rank.list/');
        $I->click('.rank-create-link');
        $I->submitForm('#frm-rankForm', ['name' => 'Legend', 'minPosts' => '100000']);
        $legend = $I->em()->getRepository(RankEntity::class)->findOneBy(['name' => 'Legend']);
        $I->assertSame(100000, $legend?->minPosts);
        $I->assertFalse($legend->isSpecial());

        $I->amOnPage('/admin.rank.create/');
        $I->submitForm('#frm-rankForm', ['name' => 'Honorary', 'minPosts' => '']);
        $honorary = $I->em()->getRepository(RankEntity::class)->findOneBy(['name' => 'Honorary']);
        $I->assertNull($honorary?->minPosts);
        $I->assertTrue($honorary->isSpecial());

        // a special rank is offered to a user, a rank by posts is not
        $user = $I->createUser('Ranked', role: 'Normal');
        $I->amOnPage('/admin.user.forum/' . $user->uuid->toString());
        $I->seeElement('#frm-forumForm option[value="' . $honorary->id . '"]');
        $I->dontSeeElement('#frm-forumForm option[value="' . $legend->id . '"]');
    }

    public function theMinimumIsUniqueAndNotNegative(IntegrationTester $I) : void
    {
        $service = $I->grabService(RankAdminService::class);
        $first = $service->save(null, 'First', 7777, null, false);

        $this->expectError($I, RankAdminService::ERROR_MIN_POSTS_TAKEN, fn () => $service->save(null, 'Second', 7777, null, false));
        $this->expectError($I, RankAdminService::ERROR_MIN_POSTS, fn () => $service->save(null, 'Negative', -1, null, false));
        $this->expectError($I, RankAdminService::ERROR_NAME, fn () => $service->save(null, ' ', null, null, false));

        $service->save($first, 'First renamed', 7777, null, false);
        $I->assertSame('First renamed', $I->reload(RankEntity::class, $first->id)?->name);
    }

    public function theImageIsStoredShownServedAndDeleted(IntegrationTester $I) : void
    {
        $service = $I->grabService(RankAdminService::class);
        $rank = $service->save(null, 'Pictured', null, $this->upload(Image::fromBlank(400, 100, ImageColor::rgb(20, 120, 20))->toString(ImageType::PNG)), false);

        $I->assertNotNull($rank->image);
        $I->assertStringStartsWith(RankImageService::SCOPE . '/', $rank->image);
        $thumbnail = (string) $I->grabService(ImagesDir::class)->get() . '/cache/ranks/_rank/' . substr($rank->image, strlen('ranks/'));
        $I->assertFileExists($thumbnail);
        $size = getimagesize($thumbnail);
        $I->assertLessThanOrEqual(200, $size[0] ?? 0);
        $I->assertLessThanOrEqual(60, $size[1] ?? 0);

        // a special rank of a user is shown at his posts and the image is served
        $I->amOnPage('/cache/ranks/_rank/' . substr($rank->image, strlen('ranks/')));
        $I->seeResponseCodeIs(200);

        $this->expectError($I, 'admin-rank.error.image.type', fn () => $service->save($rank, 'Pictured', null, $this->upload('not an image'), false));

        $service->save($rank, 'Pictured', null, null, true);
        $I->assertNull($rank->image);
        $I->assertFileDoesNotExist($thumbnail);
    }

    public function deletingASpecialRankGivesTheUsersTheRankByPosts(IntegrationTester $I) : void
    {
        $rank = $I->grabService(RankAdminService::class)->save(null, 'Gone', null, null, false);
        $user = $I->createUser('Special', role: 'Normal');
        $user->rank = $rank;
        $I->em()->flush();
        $rankId = $rank->id;
        $I->loginAsAdmin();

        $I->amOnPage('/admin.rank.delete/default/' . $rankId);
        $I->submitForm('#frm-deleteForm', []);

        $I->assertNull($I->reload(RankEntity::class, $rankId));
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->rank);
    }

    public function theRanksAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.rank.list/', 403);
        $I->seePageIsRefused('/admin.rank.create/', 403);
    }

    private function upload(string $content) : FileUpload
    {
        $tmp = (string) tempnam(sys_get_temp_dir(), 'rank');
        file_put_contents($tmp, $content);

        return new FileUpload(['name' => 'rank.png', 'full_path' => 'rank.png', 'size' => strlen($content), 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK]);
    }

    private function expectError(IntegrationTester $I, string $key, callable $call) : void
    {
        try {
            $call();
            $I->fail("Expected the error $key.");
        } catch (AdminException $exception) {
            $I->assertSame($key, $exception->getMessage());
        }
    }
}
