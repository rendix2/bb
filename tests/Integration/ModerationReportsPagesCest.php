<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\PrivateMessageEntity;
use App\Model\Entity\PrivateMessageParticipantEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Enum\ReportStatus;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\ReportService;
use Tests\Support\IntegrationTester;

/**
 * The reports for the moderators (slice 6b): a moderator sees and handles only the reports of his forums, the admin all; the
 * reporter gets a private message from the moderator in his own language. Every test runs in a transaction that is rolled back.
 */
class ModerationReportsPagesCest
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

    public function aModeratorSeesOnlyTheReportsOfHisForums(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $forumA = $this->createForum($I, 'Forum A', $moderator);
        $forumB = $this->createForum($I, 'Forum B');
        $reportA = $this->createReport($I, $forumA, 'Spam in A');
        $reportB = $this->createReport($I, $forumB, 'Spam in B');

        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.index/');
        $I->seeElement('.reports-link');
        $I->see('1', '.reports-open-count');

        $I->click('.reports-link');
        $I->seeInCurrentUrl('/forum.moderation.reports');
        $I->seeElement('#report-' . $reportA->id);
        $I->see('Spam in A');
        $I->dontSeeElement('#report-' . $reportB->id);
        $I->dontSee('Spam in B');

        // the admin sees both
        $I->amOnPage('/web.user.logout/');
        $I->loginAsAdmin();
        $I->amOnPage('/forum.moderation.reports/');
        $I->seeElement('#report-' . $reportA->id);
        $I->seeElement('#report-' . $reportB->id);
    }

    public function othersHaveNoLinkAndAre403AGuestGoesToTheLogin(IntegrationTester $I) : void
    {
        $I->amOnPage('/forum.moderation.reports/');
        $I->seeInCurrentUrl('/web.user.login');

        $user = $I->createUser('Normal', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.index/');
        $I->dontSeeElement('.reports-link');
        $I->seePageIsRefused('/forum.moderation.reports/', 403);
    }

    public function aGroupModeratorModeratesTheForumOfHisGroup(IntegrationTester $I) : void
    {
        $user = $I->createUser('GroupModerator', role: 'Normal');
        $forum = $this->createForum($I, 'Group forum');
        $other = $this->createForum($I, 'Other forum');
        $em = $I->em();

        $group = new GroupEntity();
        $group->name = 'Moderators ' . random_int(1000, 9999);
        $em->persist($group);
        $member = new UserGroupMemberEntity();
        $member->user = $user;
        $member->group = $group;
        $em->persist($member);
        $groupModerator = new GroupModeratorEntity();
        $groupModerator->group = $group;
        $groupModerator->forum = $forum;
        $em->persist($groupModerator);
        $em->flush();

        $permissions = $I->grabService(ForumPermissionService::class);
        $I->assertSame([$forum->id], $permissions->moderatedForumIds($user));
        // the same answer as isAllowed()
        $I->assertTrue($permissions->isAllowed($user, $forum, ForumPrivilege::MODERATE));
        $I->assertFalse($permissions->isAllowed($user, $other, ForumPrivilege::MODERATE));
    }

    public function theModeratorHandlesAReportAndTheReporterGetsAMessageInHisLanguage(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $forum = $this->createForum($I, 'Handled forum', $moderator);
        $report = $this->createReport($I, $forum, 'Rude words');
        $reporter = $report->user;
        $reporter->language = $this->language($I, 'en');
        $I->em()->flush();

        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.moderation.reports/');
        $I->submitForm('#frm-handleForm-' . $report->id, ['status' => ReportStatus::Resolved->value, 'note' => 'Edited the post']);

        $handled = $I->reload(ReportEntity::class, $report->id);
        $I->assertNotNull($handled);
        $I->assertSame(ReportStatus::Resolved, $handled->status);
        $I->assertSame($moderator->id, $handled->handledBy?->id);
        $I->assertNotNull($handled->handledAt);
        $I->assertSame('Edited the post', $handled->moderatorNote);

        // the private message: a new thread of the moderator with the reporter, in English (the language of the reporter)
        $participant = $I->em()->getRepository(PrivateMessageParticipantEntity::class)->findOneBy(['user' => $reporter->id]);
        $I->assertNotNull($participant);
        $I->assertFalse($participant->isRead);
        $I->assertSame($moderator->id, $participant->thread->user?->id);
        $I->assertSame('Your report in the topic “Topic of Handled forum”', $participant->thread->subject);
        $message = $I->em()->getRepository(PrivateMessageEntity::class)->findOneBy(['thread' => $participant->thread]);
        $I->assertNotNull($message);
        $I->assertStringContainsString('**resolved**', $message->text);
        $I->assertStringContainsString('/forum.topic.view/' . $handled->topic->uuid->toString(), $message->text);
        // the note is only for the moderators
        $I->assertStringNotContainsString('Edited the post', $message->text);

        // the handled tab shows the result, the open one nothing
        $I->amOnPage('/forum.moderation.reports/');
        $I->dontSeeElement('#report-' . $report->id);
        $I->amOnPage('/forum.moderation.reports/?status=handled');
        $I->see('Edited the post', '#report-' . $report->id . ' .report-note');
        $I->see($moderator->username, '#report-' . $report->id . ' .report-result');

        // only once
        $service = $I->grabService(ReportService::class);
        $this->expectError($I, ReportService::ERROR_HANDLED, fn () => $service->handle($this->user($I, $moderator), $handled, ReportStatus::Rejected, null));
    }

    public function aModeratorOfAnotherForumMayNotHandleIt(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $this->createForum($I, 'His forum', $moderator);
        $report = $this->createReport($I, $this->createForum($I, 'Foreign forum'), 'Not his');

        $service = $I->grabService(ReportService::class);
        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $service->handle($this->user($I, $moderator), $report, ReportStatus::Rejected, null));
        $this->expectError($I, ReportService::ERROR_STATUS, fn () => $service->handle($this->user($I, $I->em()->getRepository(UserEntity::class)->findOneBy(['username' => 'TestUsername']) ?? $moderator), $report, ReportStatus::Open, null));

        // the form of a foreign report does not exist for him
        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->seePageIsRefused('/forum.moderation.reports/?do=handleForm-' . $report->id . '-submit', 404);
        $I->assertSame(ReportStatus::Open, $I->reload(ReportEntity::class, $report->id)?->status);
    }

    public function theHandleFormNeedsTheToken(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $report = $this->createReport($I, $this->createForum($I, 'Token forum', $moderator), 'Token');

        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.moderation.reports/');
        $I->submitForm('#frm-handleForm-' . $report->id, ['status' => ReportStatus::Rejected->value, '_token_' => 'wrong']);
        $I->assertSame(ReportStatus::Open, $I->reload(ReportEntity::class, $report->id)?->status);
        $I->assertSame(0, $I->em()->getRepository(PrivateMessageParticipantEntity::class)->count(['user' => $report->user->id]));
    }

    private function user(IntegrationTester $I, UserEntity $user) : UserEntity
    {
        return $I->em()->find(UserEntity::class, $user->id) ?? $user;
    }

    private function language(IntegrationTester $I, string $locale) : LanguageEntity
    {
        $language = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => $locale]);
        $I->assertNotNull($language);

        return $language;
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

    private function createForum(IntegrationTester $I, string $name, ?UserEntity $moderator = null) : ForumEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Category of ' . $name;
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = $name;
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        if ($moderator !== null) {
            $moderatorEntity = new UserModeratorEntity();
            $moderatorEntity->user = $moderator;
            $moderatorEntity->forum = $forum;
            $em->persist($moderatorEntity);
        }

        $em->flush();

        return $forum;
    }

    private function createReport(IntegrationTester $I, ForumEntity $forum, string $text) : ReportEntity
    {
        $em = $I->em();
        $author = $I->createUser('Author', role: 'Normal');
        $reporter = $I->createUser('Reporter', role: 'Normal');
        $category = $forum->category ?? throw new \LogicException('A forum without a category.');

        $topic = new TopicEntity();
        $topic->name = 'Topic of ' . $forum->name;
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A reported post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);
        $em->flush();

        return $I->grabService(ReportService::class)->report($reporter, $post, $text, '127.0.0.1');
    }
}
