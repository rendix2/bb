<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\GroupForumPermissionEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege as P;
use Tests\Support\IntegrationTester;

/**
 * The permissions of the forum (slice 2): flags of the forum / groups / users, author, lock, moderators, admin.
 * Every test runs in a transaction that is rolled back.
 */
class ForumPermissionServiceCest
{
    private ForumPermissionService $service;

    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
        $this->service = $I->grabService(ForumPermissionService::class);
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function aGuestOnlyReads(IntegrationTester $I) : void
    {
        [$forum, $topic, $post] = $this->createForum($I, $I->createUser('Author', role: 'Normal'));

        $I->assertTrue($this->service->isAllowed(null, $forum, P::VIEW));
        $I->assertTrue($this->service->isAllowed(null, $topic, P::VIEW));
        $I->assertTrue($this->service->isAllowed(null, $post, P::VIEW));
        $I->assertFalse($this->service->isAllowed(null, $forum, P::TOPIC_ADD));
        $I->assertFalse($this->service->isAllowed(null, $topic, P::REPLY));
        $I->assertFalse($this->service->isAllowed(null, $post, P::EDIT));
    }

    /**
     * Regression: the old ACL had no role "Normal" (the role of every registered user in the database).
     */
    public function aNormalUserHasTheDefaultsOfTheForum(IntegrationTester $I) : void
    {
        $user = $I->createUser('Normal', role: 'Normal');
        [$forum, $topic] = $this->createForum($I, $I->createUser('Author', role: 'Normal'));

        $I->assertTrue($this->service->isAllowed($user, $forum, P::TOPIC_ADD));
        $I->assertTrue($this->service->isAllowed($user, $topic, P::REPLY));
        $I->assertTrue($this->service->isAllowed($user, $topic, P::FAST_REPLY));
        $I->assertTrue($this->service->isAllowed($user, $topic, P::THANK));
        $I->assertFalse($this->service->isAllowed($user, $forum, P::MODERATE));
    }

    public function theAuthorEditsAndDeletesOnlyHisOwnPostAccordingToTheFlags(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $other = $I->createUser('Other', role: 'Normal');
        [, $topic, $post] = $this->createForum($I, $author);

        $I->assertTrue($this->service->isAllowed($author, $post, P::EDIT));
        $I->assertTrue($this->service->isAllowed($author, $post, P::DELETE));
        $I->assertFalse($this->service->isAllowed($other, $post, P::EDIT));
        $I->assertFalse($this->service->isAllowed($other, $post, P::DELETE));
        // the defaults of a new forum do not let the author edit or delete his topic
        $I->assertFalse($this->service->isAllowed($author, $topic, P::EDIT));
        $I->assertFalse($this->service->isAllowed($author, $topic, P::DELETE));
    }

    public function aLockedTopicTakesTheRightsOfTheAuthorButNotOfTheModerator(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $moderator = $I->createUser('Moderator', role: 'Normal');
        [$forum, $topic, $post] = $this->createForum($I, $author);
        $this->makeUserModerator($I, $moderator, $forum);
        $topic->isLocked = true;
        $I->em()->flush();

        $I->assertFalse($this->service->isAllowed($author, $topic, P::REPLY));
        $I->assertFalse($this->service->isAllowed($author, $post, P::EDIT));
        $I->assertTrue($this->service->isAllowed($moderator, $topic, P::REPLY));
        $I->assertTrue($this->service->isAllowed($moderator, $post, P::EDIT));
    }

    /**
     * Regression: the old code never loaded the grants of groups (an inverted condition, a broken DQL query).
     */
    public function aGroupGrantAddsToTheDefaultsOnlyForItsMembers(IntegrationTester $I) : void
    {
        $member = $I->createUser('Member', role: 'Normal');
        $outsider = $I->createUser('Outsider', role: 'Normal');
        [$forum] = $this->createForum($I, $member);
        $forum->permissions->topicAdd = false;
        $group = $this->createGroup($I, $member, [$member]);

        $grant = new GroupForumPermissionEntity();
        $grant->group = $group;
        $grant->forum = $forum;
        $grant->permissions->topicAdd = true;
        $I->em()->persist($grant);
        $I->em()->flush();

        $I->assertTrue($this->service->isAllowed($member, $forum, P::TOPIC_ADD));
        $I->assertFalse($this->service->isAllowed($outsider, $forum, P::TOPIC_ADD));
    }

    public function aUserGrantAddsToTheDefaultsOnlyForHim(IntegrationTester $I) : void
    {
        $granted = $I->createUser('Granted', role: 'Normal');
        $other = $I->createUser('Other', role: 'Normal');
        [$forum] = $this->createForum($I, $granted);
        $forum->permissions->topicAdd = false;

        $grant = new UserForumPermissionEntity();
        $grant->user = $granted;
        $grant->forum = $forum;
        $grant->permissions->topicAdd = true;
        $I->em()->persist($grant);
        $I->em()->flush();

        $I->assertTrue($this->service->isAllowed($granted, $forum, P::TOPIC_ADD));
        $I->assertFalse($this->service->isAllowed($other, $forum, P::TOPIC_ADD));
    }

    public function moderatorsModerateOnlyTheirForum(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $userModerator = $I->createUser('UserModerator', role: 'Normal');
        $groupModerator = $I->createUser('GroupModerator', role: 'Normal');
        [$forum, , $post] = $this->createForum($I, $author);
        [$otherForum, , $otherPost] = $this->createForum($I, $author);

        $this->makeUserModerator($I, $userModerator, $forum);
        $group = $this->createGroup($I, $groupModerator, [$groupModerator]);
        $groupModeratorEntity = new GroupModeratorEntity();
        $groupModeratorEntity->group = $group;
        $groupModeratorEntity->forum = $forum;
        $I->em()->persist($groupModeratorEntity);
        $I->em()->flush();

        foreach ([$userModerator, $groupModerator] as $moderator) {
            $I->assertTrue($this->service->isAllowed($moderator, $forum, P::MODERATE));
            $I->assertTrue($this->service->isAllowed($moderator, $post, P::DELETE));
            $I->assertFalse($this->service->isAllowed($moderator, $otherForum, P::MODERATE));
            $I->assertFalse($this->service->isAllowed($moderator, $otherPost, P::DELETE));
        }
    }

    public function theAdminMayDoEverything(IntegrationTester $I) : void
    {
        $admin = $I->createUser('Admin', role: 'Admin');
        [$forum, $topic, $post] = $this->createForum($I, $I->createUser('Author', role: 'Normal'));
        $topic->isLocked = true;
        $I->em()->flush();

        $I->assertTrue($this->service->isAllowed($admin, $forum, P::MODERATE));
        $I->assertTrue($this->service->isAllowed($admin, $topic, P::REPLY));
        $I->assertTrue($this->service->isAllowed($admin, $post, P::DELETE));
    }

    /**
     * An active forum (with the defaults of a new forum) in a new category, with a topic and its first post by the author.
     *
     * @return array{ForumEntity, TopicEntity, PostEntity}
     */
    private function createForum(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Permissions category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Permissions forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Permissions topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'First post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$forum, $topic, $post];
    }

    /**
     * @param list<UserEntity> $members
     */
    private function createGroup(IntegrationTester $I, UserEntity $moderator, array $members) : GroupEntity
    {
        $group = new GroupEntity();
        $group->name = 'Permissions group';
        $group->moderator = $moderator;
        $I->em()->persist($group);

        foreach ($members as $member) {
            $membership = new UserGroupMemberEntity();
            $membership->user = $member;
            $membership->group = $group;
            $I->em()->persist($membership);
        }

        $I->em()->flush();

        return $group;
    }

    private function makeUserModerator(IntegrationTester $I, UserEntity $user, ForumEntity $forum) : void
    {
        $moderator = new UserModeratorEntity();
        $moderator->user = $user;
        $moderator->forum = $forum;
        $I->em()->persist($moderator);
        $I->em()->flush();
    }

}
