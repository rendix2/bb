<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAdminService;
use Tests\Support\IntegrationTester;

/**
 * The administration of categories and forums (slice 7a). Every test runs in a transaction that is rolled back.
 */
class AdminForumPagesCest
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

    public function theAdminCreatesACategoryAndAForumWithItsPermissions(IntegrationTester $I) : void
    {
        $I->loginAsAdmin();

        $I->amOnPage('/admin.category.list/');
        $I->click('.category-create-link');
        $I->submitForm('#frm-categoryForm', ['name' => '  Admin category  ', 'sortOrder' => '7', 'active' => '1']);
        $category = $I->em()->getRepository(CategoryEntity::class)->findOneBy(['name' => 'Admin category']);
        $I->assertNotNull($category);
        $I->assertSame(7, $category->sortOrder);
        $I->assertTrue($category->active);

        $I->amOnPage('/admin.forum.create/');
        $I->submitForm('#frm-forumForm', [
            'name' => 'Admin forum',
            'description' => '',
            'place' => 'c' . $category->id,
            'sortOrder' => '1',
            'active' => '1',
            'rules' => 'Be *nice*',
            'permissions' => ['topicAdd' => '1', 'postAdd' => '1', 'topicThank' => false],
        ]);
        $forum = $I->em()->getRepository(ForumEntity::class)->findOneBy(['name' => 'Admin forum']);
        $I->assertNotNull($forum);
        $I->assertSame($category->id, $forum->category?->id);
        $I->assertNull($forum->parent);
        $I->assertNull($forum->description);
        $I->assertSame('Be *nice*', $forum->rules);
        $I->assertTrue($forum->permissions->topicAdd);
        $I->assertTrue($forum->permissions->postAdd);
        $I->assertFalse($forum->permissions->topicThank);

        $I->amOnPage('/admin.forum.list/');
        $I->see('Admin forum');
        $I->amOnPage('/admin.category.list/');
        $I->see('Admin category');
    }

    public function movingAForumToAnotherCategoryMovesTheTopicsOfItsSubforumsToo(IntegrationTester $I) : void
    {
        $service = $I->grabService(ForumAdminService::class);
        $first = $service->saveCategory(null, 'First', 1, true);
        $second = $service->saveCategory(null, 'Second', 2, true);
        $forum = $service->saveForum(null, 'Root', null, $first, null, true, null, 1, new ForumPermissions());
        $subforum = $service->saveForum(null, 'Sub', null, null, $forum, true, null, 1, new ForumPermissions());
        $topic = $this->createTopic($I, $subforum, $first);

        $I->loginAsAdmin();
        $I->amOnPage('/admin.forum.edit/default/' . $forum->id);
        $I->submitForm('#frm-forumForm', ['place' => 'c' . $second->id]);

        $I->assertSame($second->id, $I->reload(ForumEntity::class, $forum->id)?->category?->id);
        $I->assertSame($second->id, $I->reload(TopicEntity::class, $topic->id)?->category->id);
    }

    public function aForumIsNeverInTwoPlacesNorInItsOwnSubtree(IntegrationTester $I) : void
    {
        $service = $I->grabService(ForumAdminService::class);
        $category = $service->saveCategory(null, 'Tree', 1, true);
        $forum = $service->saveForum(null, 'Root', null, $category, null, true, null, 1, new ForumPermissions());
        $subforum = $service->saveForum(null, 'Sub', null, null, $forum, true, null, 1, new ForumPermissions());

        $this->expectError($I, ForumAdminService::ERROR_PLACE, fn () => $service->saveForum($forum, 'Root', null, $category, $subforum, true, null, 1, new ForumPermissions()));
        $this->expectError($I, ForumAdminService::ERROR_PLACE, fn () => $service->saveForum($forum, 'Root', null, null, null, true, null, 1, new ForumPermissions()));
        $this->expectError($I, ForumAdminService::ERROR_CYCLE, fn () => $service->saveForum($forum, 'Root', null, null, $subforum, true, null, 1, new ForumPermissions()));
        $this->expectError($I, ForumAdminService::ERROR_CYCLE, fn () => $service->saveForum($forum, 'Root', null, null, $forum, true, null, 1, new ForumPermissions()));
        $this->expectError($I, ForumAdminService::ERROR_NAME, fn () => $service->saveCategory(null, '   ', 1, true));

        // the edit form does not offer the forum itself nor its subforum as the parent
        $I->loginAsAdmin();
        $I->amOnPage('/admin.forum.edit/default/' . $forum->id);
        $I->dontSeeElement('#frm-forumForm option[value="f' . $forum->id . '"]');
        $I->dontSeeElement('#frm-forumForm option[value="f' . $subforum->id . '"]');
        $I->amOnPage('/admin.forum.edit/default/' . $subforum->id);
        $I->seeElement('#frm-forumForm option[value="f' . $forum->id . '"]');
    }

    public function onlyAnEmptyCategoryOrForumIsDeleted(IntegrationTester $I) : void
    {
        $service = $I->grabService(ForumAdminService::class);
        $category = $service->saveCategory(null, 'Delete me', 1, true);
        $forum = $service->saveForum(null, 'Full', null, $category, null, true, null, 1, new ForumPermissions());
        $topic = $this->createTopic($I, $forum, $category);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.forum.delete/default/' . $forum->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->see('Fórum nelze smazat');
        $I->amOnPage('/admin.category.delete/default/' . $category->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->see('Kategorii nelze smazat');
        $I->assertNotNull($I->reload(TopicEntity::class, $topic->id));

        $I->em()->remove($I->reload(TopicEntity::class, $topic->id) ?? $topic);
        $I->em()->flush();

        $I->amOnPage('/admin.forum.delete/default/' . $forum->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertNull($I->reload(ForumEntity::class, $forum->id));
        $I->amOnPage('/admin.category.delete/default/' . $category->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertNull($I->reload(CategoryEntity::class, $category->id));
    }

    public function theAdministrationIsOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $category = $I->grabService(ForumAdminService::class)->saveCategory(null, 'Closed', 1, true);
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        foreach (['/admin.category.list/', '/admin.category.create/', '/admin.category.edit/default/' . $category->id, '/admin.category.delete/default/' . $category->id, '/admin.forum.list/', '/admin.forum.create/'] as $url) {
            $I->seePageIsRefused($url, 403);
        }

        $I->assertNotNull($I->reload(CategoryEntity::class, $category->id));
    }

    private function createTopic(IntegrationTester $I, ForumEntity $forum, CategoryEntity $category) : TopicEntity
    {
        $author = $I->createUser('Author', role: 'Normal');
        $topic = new TopicEntity();
        $topic->name = 'Admin topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $I->em()->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $I->em()->persist($post);
        $I->em()->flush();

        return $topic;
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
