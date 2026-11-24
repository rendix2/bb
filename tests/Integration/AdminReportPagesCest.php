<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Enum\ReportStatus;
use App\Model\Service\ReportService;
use Tests\Support\IntegrationTester;

/**
 * The overview and the reports in the administration (slice 7e1). Every test runs in a transaction that is rolled back.
 */
class AdminReportPagesCest
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

    public function theAdminHandlesAReportInTheAdministrationAndNobodyDeletesIt(IntegrationTester $I) : void
    {
        $report = $this->createReport($I, 'Spam in the administration');
        $I->loginAsAdmin();

        $I->amOnPage('/admin.report.list/');
        $I->see('Spam in the administration', '#report-' . $report->id);
        $I->dontSeeElement('#report-' . $report->id . ' a[href*="delete"]');
        $I->submitForm('#frm-handleForm-' . $report->id, ['status' => ReportStatus::Rejected->value, 'note' => 'Not spam']);

        $handled = $I->reload(ReportEntity::class, $report->id);
        $I->assertSame(ReportStatus::Rejected, $handled?->status);
        $I->assertSame('Not spam', $handled->moderatorNote);
        $I->seeInCurrentUrl('/admin.report.list');

        $I->amOnPage('/admin.report.list/?status=handled');
        $I->see('Spam in the administration', '#report-' . $report->id);
    }

    public function theDashboardCountsTheForum(IntegrationTester $I) : void
    {
        $this->createReport($I, 'Counted');
        $I->loginAsAdmin();

        $I->amOnPage('/admin.dashboard/');

        $open = $I->em()->getRepository(ReportEntity::class)->count(['status' => ReportStatus::Open]);
        $I->see((string) $open, '.dashboard-openReports .dashboard-value');
        $I->see((string) $I->em()->getRepository(PostEntity::class)->count([]), '.dashboard-posts .dashboard-value');
    }

    public function theOverviewAndReportsAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.dashboard/', 403);
        $I->seePageIsRefused('/admin.report.list/', 403);
    }

    private function createReport(IntegrationTester $I, string $text) : ReportEntity
    {
        $em = $I->em();
        $author = $I->createUser('Author', role: 'Normal');
        $reporter = $I->createUser('Reporter', role: 'Normal');

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
        $forum->permissions = new ForumPermissions();
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Reported topic';
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
