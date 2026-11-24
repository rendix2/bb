<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\GroupForumPermissionEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAccessAdminService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Tests\Support\IntegrationTester;

/**
 * The administration of groups and of the access to forums (slice 7b). Every test runs in a transaction that is rolled back.
 */
class AdminGroupPagesCest
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

    public function theAdminCreatesAGroupAndManagesItsMembers(IntegrationTester $I) : void
    {
        $member = $I->createUser('Member', role: 'Normal');
        $I->loginAsAdmin();

        $I->amOnPage('/admin.group.list/');
        $I->click('.group-create-link');
        $I->submitForm('#frm-groupForm', ['name' => 'Admin group']);
        $group = $I->em()->getRepository(GroupEntity::class)->findOneBy(['name' => 'Admin group']);
        $I->assertNotNull($group);
        $I->seeInCurrentUrl('/admin.group.edit/default/' . $group->id);

        $I->submitForm('#frm-addMemberForm', ['username' => mb_strtoupper($member->username)]);
        $I->assertSame(1, $I->em()->getRepository(UserGroupMemberEntity::class)->count(['group' => $group->id, 'user' => $member->id]));
        $I->see($member->username, '.group-member');

        $I->submitForm('#frm-addMemberForm', ['username' => $member->username]);
        $I->see('už ve skupině je');
        $I->submitForm('#frm-addMemberForm', ['username' => 'nobody-' . bin2hex(random_bytes(4))]);
        $I->see('neexistuje');

        $I->submitForm('#frm-removeMemberForm-' . $member->id, []);
        $I->assertSame(0, $I->em()->getRepository(UserGroupMemberEntity::class)->count(['group' => $group->id]));
    }

    public function theAccessOfAGroupAddsPermissionsAndModeration(IntegrationTester $I) : void
    {
        $member = $I->createUser('Member', role: 'Normal');
        $forum = $this->createForum($I);
        $service = $I->grabService(ForumAccessAdminService::class);
        $group = $service->saveGroup(null, 'Access group');
        $service->addMember($group, $member->username);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.group.edit/default/' . $group->id);
        $I->submitForm('#frm-accessForm', ['a' . $forum->id . '_topicAdd' => true, 'a' . $forum->id . '_moderator' => true]);

        $grant = $I->em()->getRepository(GroupForumPermissionEntity::class)->findOneBy(['group' => $group->id, 'forum' => $forum->id]);
        $I->assertTrue($grant?->permissions->topicAdd);
        $I->assertFalse($grant->permissions->postAdd);
        $I->assertSame(1, $I->em()->getRepository(GroupModeratorEntity::class)->count(['group' => $group->id, 'forum' => $forum->id]));
        $I->assertTrue($I->grabService(ForumPermissionService::class)->isAllowed($I->reload(UserEntity::class, $member->id), $I->reload(ForumEntity::class, $forum->id) ?? $forum, ForumPrivilege::MODERATE));

        // nothing checked: the grant and the moderation are gone
        $I->amOnPage('/admin.group.edit/default/' . $group->id);
        $I->submitForm('#frm-accessForm', ['a' . $forum->id . '_topicAdd' => false, 'a' . $forum->id . '_moderator' => false]);
        $I->assertSame(0, $I->em()->getRepository(GroupForumPermissionEntity::class)->count(['group' => $group->id]));
        $I->assertSame(0, $I->em()->getRepository(GroupModeratorEntity::class)->count(['group' => $group->id]));
    }

    public function theAccessPageOfAForumSetsModeratorsAndGrantsOfUsers(IntegrationTester $I) : void
    {
        $user = $I->createUser('Granted', role: 'Normal');
        $forum = $this->createForum($I);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.forum.access/default/' . $forum->id);
        $I->submitForm('#frm-addModeratorForm', ['username' => $user->username]);
        $moderator = $I->em()->getRepository(UserModeratorEntity::class)->findOneBy(['forum' => $forum->id, 'user' => $user->id]);
        $I->assertNotNull($moderator);
        $I->submitForm('#frm-addModeratorForm', ['username' => $user->username]);
        $I->see('už fórum moderuje');

        $I->submitForm('#frm-grantForm', ['username' => $user->username, 'permissions' => ['postAdd' => true, 'topicThank' => true]]);
        $grant = $I->em()->getRepository(UserForumPermissionEntity::class)->findOneBy(['forum' => $forum->id, 'user' => $user->id]);
        $I->assertTrue($grant?->permissions->postAdd);
        $I->assertTrue($grant->permissions->topicThank);
        $I->assertFalse($grant->permissions->topicAdd);
        $I->see($user->username, '.forum-grant');

        $I->submitForm('#frm-removeGrantForm-' . $grant->id, []);
        $I->assertSame(0, $I->em()->getRepository(UserForumPermissionEntity::class)->count(['forum' => $forum->id]));
        $I->submitForm('#frm-removeModeratorForm-' . $moderator->id, []);
        $I->assertSame(0, $I->em()->getRepository(UserModeratorEntity::class)->count(['forum' => $forum->id]));
    }

    public function theNameOfAGroupIsUniqueRegardlessOfTheCase(IntegrationTester $I) : void
    {
        $service = $I->grabService(ForumAccessAdminService::class);
        $group = $service->saveGroup(null, 'Unique group');

        try {
            $service->saveGroup(null, 'UNIQUE GROUP');
            $I->fail('A second group of the same name.');
        } catch (AdminException $exception) {
            $I->assertSame(ForumAccessAdminService::ERROR_GROUP_EXISTS, $exception->getMessage());
        }

        $service->saveGroup($group, 'Unique group');

        // the database too (UK__Groups__Lower_name)
        $connection = $I->em()->getConnection();
        $connection->beginTransaction();

        try {
            $connection->createQueryBuilder()
                ->insert('groups')
                ->values(['uuid' => ':uuid', 'name' => ':name', 'created_at' => 'NOW()'])
                ->setParameter('uuid', '00000000-0000-4000-8000-000000000001')
                ->setParameter('name', 'unique GROUP')
                ->executeStatement();
            $I->fail('The database accepted the same name.');
        } catch (UniqueConstraintViolationException) {
            // expected
        } finally {
            $connection->rollBack();
        }
    }

    public function theAdministrationOfGroupsIsOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $group = $I->grabService(ForumAccessAdminService::class)->saveGroup(null, 'Closed group');
        $forum = $this->createForum($I);
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        foreach (['/admin.group.list/', '/admin.group.create/', '/admin.group.edit/default/' . $group->id, '/admin.group.delete/default/' . $group->id, '/admin.forum.access/default/' . $forum->id] as $url) {
            $I->seePageIsRefused($url, 403);
        }
    }

    public function deletingAGroupKeepsItsUsers(IntegrationTester $I) : void
    {
        $member = $I->createUser('Member', role: 'Normal');
        $service = $I->grabService(ForumAccessAdminService::class);
        $group = $service->saveGroup(null, 'Doomed group');
        $service->addMember($group, $member->username);
        $forum = $this->createForum($I);
        $permissions = new ForumPermissions();
        $permissions->topicAdd = true;
        $service->setGroupAccess($group, $forum, $permissions, true);
        $groupId = $group->id;
        $I->loginAsAdmin();

        $I->amOnPage('/admin.group.delete/default/' . $groupId);
        $I->submitForm('#frm-deleteForm', []);

        // (Doctrine unsets the id of a removed entity)
        $I->assertNull($I->reload(GroupEntity::class, $groupId));
        $I->assertNotNull($I->reload(UserEntity::class, $member->id));
        $I->assertSame(0, $I->em()->getRepository(GroupForumPermissionEntity::class)->count(['forum' => $forum->id]));
    }

    private function createForum(IntegrationTester $I) : ForumEntity
    {
        $category = new CategoryEntity();
        $category->name = 'Access category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $I->em()->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Access forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $forum->permissions = new ForumPermissions();
        $I->em()->persist($forum);
        $I->em()->flush();

        return $forum;
    }
}
