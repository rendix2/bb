<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\BanEntity;
use App\Model\Entity\BanReasonEntity;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\AdminException;
use App\Model\Service\BanAdminService;
use App\Model\Service\BanService;
use App\Model\Service\PrivateMessageException;
use App\Model\Service\PrivateMessageService;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Bans (slice 7c1): the administration and the enforcement of every scope. Every test runs in a transaction that is rolled back.
 */
class AdminBanPagesCest
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

    public function theAdminBansAUserWithAPredefinedReason(IntegrationTester $I) : void
    {
        $user = $I->createUser('Banned', role: 'Normal');
        $I->grabService(BanAdminService::class)->saveReason(null, 'Spam', 1);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.ban.list/');
        $I->click('.ban-create-link');
        $I->submitForm('#frm-banForm', ['username' => mb_strtoupper($user->username), 'reasonPreset' => 'Spam', 'reason' => '']);

        $ban = $I->em()->getRepository(BanEntity::class)->findOneBy(['user' => $user->id]);
        $I->assertNotNull($ban);
        $I->assertSame('Spam', $ban->reason);
        $I->assertSame(BanScope::Web, $ban->scope, 'The whole web is the default.');
        $I->assertNull($ban->expiresAt);
        $I->assertNotNull($ban->createdBy);

        $I->amOnPage('/admin.ban.edit/default/' . $ban->id);
        $I->submitForm('#frm-banForm', ['scope' => BanScope::Writing->value, 'reason' => 'Own words', 'expiresAt' => '2099-01-01T12:00']);
        $ban = $I->reload(BanEntity::class, $ban->id);
        $I->assertSame(BanScope::Writing, $ban?->scope);
        $I->assertSame('Own words', $ban->reason);
        $I->assertSame('2099-01-01 12:00', $ban->expiresAt?->format('Y-m-d H:i'));

        $I->amOnPage('/admin.ban.delete/default/' . $ban->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertSame(0, $I->em()->getRepository(BanEntity::class)->count(['user' => $user->id]));
    }

    public function aBannedUserIsLoggedOutAndSeesTheBan(IntegrationTester $I) : void
    {
        $user = $I->createUser('Banned', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);
        $ban = $this->ban($I, $user->username, null, null, BanScope::Login, 'Go away');

        $I->amOnPage('/forum.index/');

        $I->seeInCurrentUrl('/web.banned/' . $ban->uuid->toString());
        $I->see('Go away', '.ban-reason');
        $I->assertFalse($I->grabService(\Nette\Security\User::class)->isLoggedIn());
    }

    public function theScopesBlockOnlyWhatTheySay(IntegrationTester $I) : void
    {
        $writer = $I->createUser('Writer', role: 'Normal');
        $forum = $this->createForum($I);
        $this->ban($I, $writer->username, null, null, BanScope::Writing, 'No writing');
        $permissions = $I->grabService(ForumPermissionService::class);
        $writerEntity = $this->find($I, $writer);

        $I->assertTrue($permissions->isAllowed($writerEntity, $forum, ForumPrivilege::VIEW));
        $I->assertFalse($permissions->isAllowed($writerEntity, $forum, ForumPrivilege::TOPIC_ADD));

        try {
            $I->grabService(PrivateMessageService::class)->createThread($writerEntity, 'Hi', [$I->createUser('Other', role: 'Normal')->username], 'A message text');
            $I->fail('A banned writer wrote a private message.');
        } catch (PrivateMessageException $exception) {
            $I->assertSame(PrivateMessageService::ERROR_BANNED, $exception->getMessage());
        }

        // the registration: an e-mail ban of the registration, the whole web blocks it too, a ban of the login does not
        $bans = $I->grabService(BanService::class);
        $this->ban($I, null, 'Spammer@Example.com', null, BanScope::Registration, 'No account');
        $this->ban($I, null, null, '10.0.0.7', BanScope::Login, 'No login');
        $this->ban($I, null, null, '10.0.0.8', BanScope::Web, 'Nothing');
        $I->assertNotNull($bans->find(null, 'spammer@example.COM', '10.0.0.1', BanScope::Registration));
        $I->assertNull($bans->find(null, 'other@example.com', '10.0.0.7', BanScope::Registration));
        $I->assertNotNull($bans->find(null, 'other@example.com', '10.0.0.8', BanScope::Registration));
        $I->assertNull($bans->find(null, 'spammer@example.com', '10.0.0.1', BanScope::Web), 'A ban of the registration does not block the web.');
    }

    public function anExpiredBanAndAnAdminAreNeverBanned(IntegrationTester $I) : void
    {
        $user = $I->createUser('Former', role: 'Normal');
        $ban = $this->ban($I, $user->username, null, null, BanScope::Web, 'Old');
        $ban->expiresAt = new DateTimeImmutable('-1 minute');
        $I->em()->flush();
        $bans = $I->grabService(BanService::class);
        $bans->forget();
        $I->assertNull($bans->find($this->find($I, $user), null, null, BanScope::Web));

        $admin = $I->em()->getRepository(UserEntity::class)->findOneBy(['username' => 'TestUsername']) ?? $I->fail('No admin.');

        try {
            $this->ban($I, $admin->username, null, null, BanScope::Web, 'Lock out');
            $I->fail('An admin was banned.');
        } catch (AdminException $exception) {
            $I->assertSame(BanAdminService::ERROR_ADMIN, $exception->getMessage());
        }

        // a ban of his e-mail or IP address does not stop him either
        $this->ban($I, null, $admin->email, '10.0.0.9', BanScope::Web, 'Lock out');
        $I->assertNull($bans->find($admin, null, '10.0.0.9', BanScope::Web));

        $this->expectError($I, BanAdminService::ERROR_TARGET, fn () => $this->ban($I, null, null, null, BanScope::Web, 'Nobody'));
        $this->expectError($I, BanAdminService::ERROR_IP, fn () => $this->ban($I, null, null, '999.1.1.1', BanScope::Web, 'Bad'));
        $this->expectError($I, BanAdminService::ERROR_REASON, fn () => $this->ban($I, null, null, '10.0.0.1', BanScope::Web, '  '));
    }

    public function thePredefinedReasonsAreManaged(IntegrationTester $I) : void
    {
        $I->loginAsAdmin();

        $I->amOnPage('/admin.ban-reason.list/');
        $I->submitForm('#frm-reasonForm', ['text' => 'Offensive posts', 'sortOrder' => '2']);
        $reason = $I->em()->getRepository(BanReasonEntity::class)->findOneBy(['text' => 'Offensive posts']);
        $I->assertNotNull($reason);
        $I->see('Offensive posts', '.ban-reason');

        $reasonId = $reason->id;
        $I->amOnPage('/admin.ban-reason.delete/default/' . $reasonId);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertNull($I->reload(BanReasonEntity::class, $reasonId));
    }

    public function theBansAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        foreach (['/admin.ban.list/', '/admin.ban.create/', '/admin.ban-reason.list/'] as $url) {
            $I->seePageIsRefused($url, 403);
        }
    }

    private function ban(IntegrationTester $I, ?string $username, ?string $email, ?string $ipAddress, BanScope $scope, string $reason) : BanEntity
    {
        $admin = $I->em()->getRepository(UserEntity::class)->findOneBy(['username' => 'TestUsername']) ?? $I->fail('No admin.');

        return $I->grabService(BanAdminService::class)->saveBan(null, $username, $email, $ipAddress, $scope, $reason, null, $admin);
    }

    private function find(IntegrationTester $I, UserEntity $user) : UserEntity
    {
        return $I->em()->find(UserEntity::class, $user->id) ?? $I->fail('The user is gone.');
    }

    private function createForum(IntegrationTester $I) : ForumEntity
    {
        $category = new CategoryEntity();
        $category->name = 'Ban category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $I->em()->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Ban forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $forum->permissions = new ForumPermissions();
        $forum->permissions->grantDefaultsOfANewForum();
        $I->em()->persist($forum);
        $I->em()->flush();

        return $forum;
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
