<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\LanguageEntity;
use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Service\AdminException;
use App\Model\Service\BanAdminService;
use App\Model\Service\ForumAccessAdminService;
use App\Model\Service\UserForumAdminService;
use Tests\Support\IntegrationTester;

/**
 * The forum side of a user in the administration (slice 7c2, Admin:User:Forum). Every test runs in a transaction that is
 * rolled back.
 */
class AdminUserForumPagesCest
{
    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function theAdminSetsTheLanguageSignatureAndSpecialRank(IntegrationTester $I) : void
    {
        $user = $I->createUser('Forumer', role: 'Normal');
        $english = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'en']) ?? $I->fail('No English.');
        $rank = $I->em()->getRepository(RankEntity::class)->findOneBy([]) ?? $I->fail('No rank.');
        $I->loginAsAdmin();

        $I->amOnPage('/admin.user.edit/' . $user->uuid->toString());
        $I->click('.user-forum-link');
        $I->seeInCurrentUrl('/admin.user.forum/' . $user->uuid->toString());
        $I->submitForm('#frm-forumForm', ['language' => (string) $english->id, 'signature' => '  *Hi*  ', 'rank' => (string) $rank->id]);

        $saved = $I->reload(UserEntity::class, $user->id);
        $I->assertSame('en', $saved?->language->locale);
        $I->assertSame('*Hi*', $saved->signature);
        $I->assertSame($rank->id, $saved->rank?->id);

        // back to the rank by the post count and no signature
        $I->amOnPage('/admin.user.forum/' . $user->uuid->toString());
        $I->submitForm('#frm-forumForm', ['signature' => '', 'rank' => '']);
        $saved = $I->reload(UserEntity::class, $user->id);
        $I->assertNull($saved?->signature);
        $I->assertNull($saved->rank);

        $service = $I->grabService(UserForumAdminService::class);

        try {
            $service->save($saved, $english, str_repeat('x', $service->signatureMaxLength() + 1), null);
            $I->fail('A too long signature was saved.');
        } catch (AdminException $exception) {
            $I->assertSame(UserForumAdminService::ERROR_SIGNATURE, $exception->getMessage());
        }
    }

    public function thePageShowsGroupsBansAndDeletesTheAvatar(IntegrationTester $I) : void
    {
        $user = $I->createUser('Shown', role: 'Normal');
        $user->avatar = 'elsewhere/not-a-file';
        $I->em()->flush();
        $access = $I->grabService(ForumAccessAdminService::class);
        $group = $access->saveGroup(null, 'Shown group');
        $access->addMember($group, $user->username);
        $admin = $I->em()->getRepository(UserEntity::class)->findOneBy(['username' => 'TestUsername']) ?? $I->fail('No admin.');
        $I->grabService(BanAdminService::class)->saveBan(null, $user->username, null, null, BanScope::Writing, 'Too loud', null, $admin);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.user.forum/' . $user->uuid->toString());
        $I->see('Shown group', '.user-groups');
        $I->see('Too loud', '.user-bans');

        $I->submitForm('#frm-deleteAvatarForm', []);
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->avatar);

        $I->click('.user-ban-link');
        $I->seeInField('username', $user->username);
    }

    public function theForumSideOfAUserIsOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.user.forum/' . $user->uuid->toString(), 403);
    }
}
