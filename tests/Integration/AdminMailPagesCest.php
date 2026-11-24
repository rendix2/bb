<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\MailEntity;
use App\Model\Entity\MassMailEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\MassMailService;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Mass e-mails of the administration (slice 7e4): to all active users through the queue, the history and the queue in the
 * administration. Rolled back after each test.
 */
class AdminMailPagesCest
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

    public function theAdminSendsAMassMailToAllActiveUsers(IntegrationTester $I) : void
    {
        $active = $I->createUser('MassActive', role: 'Normal');
        $inactive = $I->createUser('MassInactive', false, 'Normal');
        $activeUsers = $I->em()->getRepository(UserEntity::class)->count(['isActive' => true]);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.mail.list/');
        $I->click('.mail-create-link');
        $I->see((string) $activeUsers, '.mail-recipient-count');
        $I->submitForm('#frm-mailForm', ['subject' => 'Maintenance tonight', 'body' => 'The forum is **down** at 22:00 :warning:']);

        $massMail = $I->em()->getRepository(MassMailEntity::class)->findOneBy(['subject' => 'Maintenance tonight']) ?? $I->fail('Not in the history.');
        $I->assertSame($activeUsers, $massMail->recipientCount);
        $I->assertSame('TestUsername', $massMail->sentBy?->username);

        $mail = $I->em()->getRepository(MailEntity::class)->findOneBy(['emailTo' => $active->email, 'subject' => 'Maintenance tonight']) ?? $I->fail('Not queued.');
        $I->assertNull($mail->sentAt);
        $I->assertStringContainsString('<strong>down</strong>', $mail->body);
        $I->assertStringContainsString('⚠️', $mail->body);
        $I->assertNull($I->em()->getRepository(MailEntity::class)->findOneBy(['emailTo' => $inactive->email]));
        $I->assertSame($activeUsers, $I->em()->getRepository(MailEntity::class)->count(['subject' => 'Maintenance tonight']));

        $I->amOnPage('/admin.mail.list/');
        $I->see('Maintenance tonight', '.mass-mail');
        $I->amOnPage('/admin.mail.queue/');
        $I->see($active->email, '.queued-mail');
    }

    public function theDashboardCountsOnlyTheQueuedMails(IntegrationTester $I) : void
    {
        $service = $I->grabService(MassMailService::class);
        $sent = new MailEntity();
        $sent->emailTo = 'sent@example.com';
        $sent->subject = 'Sent';
        $sent->body = 'x';
        $sent->sentAt = new DateTimeImmutable();
        $I->em()->persist($sent);
        $I->em()->flush();

        $I->loginAsAdmin();
        $I->amOnPage('/admin.dashboard/');
        $I->see((string) $service->queueCount(), '.dashboard-mailQueue .dashboard-value');
        $I->assertNotSame($service->queueCount(), $I->em()->getRepository(MailEntity::class)->count([]));
    }

    public function theSubjectAndTheTextAreRequired(IntegrationTester $I) : void
    {
        $service = $I->grabService(MassMailService::class);
        $admin = $I->em()->getRepository(UserEntity::class)->findOneBy(['username' => 'TestUsername']) ?? $I->fail('No admin.');

        foreach ([[' ', 'Text', MassMailService::ERROR_SUBJECT], [str_repeat('s', 1025), 'Text', MassMailService::ERROR_SUBJECT], ['Subject', ' ', MassMailService::ERROR_BODY]] as [$subject, $body, $error]) {
            try {
                $service->send($admin, $subject, $body);
                $I->fail('Sent without ' . $error);
            } catch (AdminException $exception) {
                $I->assertSame($error, $exception->getMessage());
            }
        }

        $I->assertSame(0, $I->em()->getRepository(MassMailEntity::class)->count([]));
    }

    public function theMailsAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.mail.list/', 403);
        $I->seePageIsRefused('/admin.mail.create/', 403);
        $I->seePageIsRefused('/admin.mail.queue/', 403);
    }
}
