<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ReportStatus;
use App\Model\Service\PostWriteException;
use App\Model\Service\ReportService;
use Tests\Support\IntegrationTester;

/**
 * Reporting a post (slice 3n): a logged-in user, not his own post, once per post. Every test runs in a transaction that
 * is rolled back.
 */
class ReportPagesCest
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

    public function aUserReportsAPostOnce(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('#post-' . $post->id . ' .post-report-link');
        $I->submitForm('#frm-reportForm', ['text' => 'Spam with links']);

        $I->seeInCurrentUrl('/forum.topic.view/' . $topic->uuid->toString());
        $report = $I->em()->getRepository(ReportEntity::class)->findOneBy(['post' => $post->id]);
        $I->assertNotNull($report);
        $I->assertSame('Spam with links', $report->reportText);
        $I->assertSame(ReportStatus::Open, $report->status);
        $I->assertSame($reader->id, $report->user->id);
        $I->assertSame($topic->id, $report->topic->id);

        // the second time the page only says it was reported, the service refuses it
        $I->amOnPage('/forum.post.report/' . $post->uuid->toString());
        $I->seeElement('.report-already');
        $I->dontSeeElement('#frm-reportForm');

        $service = $I->grabService(ReportService::class);
        $this->expectError($I, ReportService::ERROR_ALREADY, fn () => $service->report($this->find($I, $reader), $this->findPost($I, $post), 'Again', '127.0.0.1'));
        $I->assertSame(1, $I->em()->getRepository(ReportEntity::class)->count(['post' => $post->id]));
    }

    public function theAuthorCannotReportHisPostAndAGuestIsSentToLogin(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#post-' . $post->id . ' .post-report-link');
        $I->amOnPage('/forum.post.report/' . $post->uuid->toString());
        $I->seeInCurrentUrl('/web.user.login');

        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#post-' . $post->id . ' .post-report-link');
        $I->seePageIsRefused('/forum.post.report/' . $post->uuid->toString(), 403);

        $service = $I->grabService(ReportService::class);
        $this->expectError($I, ReportService::ERROR_OWN, fn () => $service->report($this->find($I, $author), $this->findPost($I, $post), 'Mine', '127.0.0.1'));
    }

    public function anEmptyOrTooLongReasonIsRefused(IntegrationTester $I) : void
    {
        [, $post] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $reader = $I->createUser('Reader', role: 'Normal');
        $service = $I->grabService(ReportService::class);

        $this->expectError($I, ReportService::ERROR_EMPTY, fn () => $service->report($this->find($I, $reader), $this->findPost($I, $post), "  \n ", '127.0.0.1'));
        $this->expectError($I, ReportService::ERROR_TOO_LONG, fn () => $service->report($this->find($I, $reader), $this->findPost($I, $post), str_repeat('x', ReportService::TEXT_MAX_LENGTH + 1), '127.0.0.1'));
        $I->assertSame(0, $I->em()->getRepository(ReportEntity::class)->count(['post' => $post->id]));
    }

    private function find(IntegrationTester $I, UserEntity $user) : UserEntity
    {
        return $I->em()->find(UserEntity::class, $user->id) ?? $user;
    }

    private function findPost(IntegrationTester $I, PostEntity $post) : PostEntity
    {
        return $I->em()->find(PostEntity::class, $post->id) ?? $post;
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

    /**
     * @return array{TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Report category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Report forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Report topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post to report';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $post];
    }
}
