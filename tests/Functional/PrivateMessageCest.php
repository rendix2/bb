<?php declare(strict_types=1);

namespace Tests\Functional;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\MailEntity;
use App\Model\Entity\PrivateMessageThreadEntity;
use Tests\Support\FunctionalTester;

/**
 * Private messages over HTTP (slice 5): a new thread with the CSRF token of the form, the recipient sees the unread count in the
 * layout of the forum, reads the thread and replies; an outsider gets 404. The threads (they outlive their users: founder SET
 * NULL) and the queued mails of the test are removed afterwards (the tests run against the dev database).
 */
class PrivateMessageCest
{
    private string $subject = '';

    /** @var list<string> */
    private array $emails = [];

    public function _after(FunctionalTester $I) : void
    {
        $em = $I->grabService(EntityManagerDecorator::class);
        $em->getRepository(PrivateMessageThreadEntity::class)->createQueryBuilder('_t')
            ->delete()
            ->where('_t.subject = :subject')
            ->setParameter('subject', $this->subject)
            ->getQuery()
            ->execute();

        if ($this->emails !== []) {
            $em->getRepository(MailEntity::class)->createQueryBuilder('_m')
                ->delete()
                ->where('_m.emailTo IN (:emails)')
                ->setParameter('emails', $this->emails)
                ->getQuery()
                ->execute();
        }
    }

    public function aThreadIsSentReadAndAnswered(FunctionalTester $I) : void
    {
        $author = $I->createUser('pmauthor');
        $anna = $I->createUser('pmanna');
        $outsider = $I->createUser('pmoutsider');
        $this->subject = 'Functional thread ' . random_int(100000, 999999);
        $this->emails = [$author->email, $anna->email, $outsider->email];

        $I->amOnPage('/forum.pm.list/');
        $I->seePageIsOk('list of a guest');
        $I->seeElement('#frm-loginForm');

        $I->login($author->username, FunctionalTester::PASSWORD);
        $I->amOnPage('/forum.pm.create/?to=' . $anna->username);
        $I->seePageIsOk('new thread');
        $I->submitForm('#frm-createForm', ['subject' => $this->subject, 'text' => 'Hello **Anna**']);
        $I->seePageIsOk('thread after sending');
        $I->seeInSource('<strong>Anna</strong>');
        $url = $I->grabFromCurrentUrl();

        $I->amOnPage('/web.user.logout/');
        $I->login($outsider->username, FunctionalTester::PASSWORD);
        $I->amOnPage($url);
        $I->seeRefused(404, 'thread of others');

        $I->amOnPage('/web.user.logout/');
        $I->login($anna->username, FunctionalTester::PASSWORD);
        $I->amOnPage('/forum.index/');
        $I->seePageIsOk('index');
        $I->see('1', '.pm-unread-count');
        $I->amOnPage('/forum.pm.list/');
        $I->click($this->subject);
        $I->seePageIsOk('thread of the recipient');
        $I->submitForm('#frm-replyForm', ['text' => 'Hello *back*']);
        $I->seePageIsOk('thread after the reply');
        $I->seeInSource('<em>back</em>');
        $I->amOnPage('/forum.index/');
        $I->dontSeeElement('.pm-unread-count');
    }
}
