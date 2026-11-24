<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\PrivateMessageEntity;
use App\Model\Entity\PrivateMessageParticipantEntity;
use App\Model\Entity\PrivateMessageThreadEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PrivateMessageService;
use Tests\Support\IntegrationTester;

/**
 * The pages of private messages (slice 5c): Forum:Pm:List, Forum:Pm:Create, Forum:Pm:Thread – only logged-in users, a thread
 * only for its active participants (404), changes by POST + CSRF, the unread count in the layout of the forum. Every test runs
 * in a transaction that is rolled back.
 */
class PrivateMessagePagesCest
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

    /**
     * From the profile to a new thread with two recipients, the message in Markdown; the recipient sees the unread count, opening
     * the thread reads it.
     */
    public function aThreadIsWrittenAndRead(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');

        $I->amOnPage('/forum.pm.list/');
        $I->seeElement('#frm-loginForm');
        $I->amOnPage('/forum.pm.create/');
        $I->seeElement('#frm-loginForm');

        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.pm.list/');
        $I->see('Zatím nemáte žádné soukromé zprávy.');
        $I->amOnPage('/web.user.view/' . $anna->uuid->toString());
        $I->click('.profile-pm-link');
        $I->seeInField('#frm-createForm input[name=recipients]', $anna->username);
        $I->submitForm('#frm-createForm', [
            'recipients' => mb_strtolower($anna->username) . ', ' . $bob->username,
            'subject' => 'Trip',
            'text' => 'Let us go **tomorrow**',
        ]);
        $thread = $I->em()->getRepository(PrivateMessageThreadEntity::class)->findOneBy(['user' => $author]);
        $I->assertNotNull($thread);
        $I->seeInCurrentUrl('/forum.pm.thread/' . $thread->uuid->toString());
        $I->see('tomorrow', '.pm-message-text strong');
        $I->seeNumberOfElements('.pm-participant', 3);
        $I->seeElement('#frm-addParticipantsForm');
        $I->dontSeeElement('.pm-unread-count');

        $I->amOnPage('/web.user.logout/');
        $I->login($anna->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.index/');
        $I->see('1', '.pm-link .pm-unread-count');
        $I->click('.pm-link');
        $I->seeElement('tr.pm-thread-unread');
        $I->click('.pm-thread-link');
        $I->see('Trip', 'h1');
        $I->dontSeeElement('#frm-addParticipantsForm');
        $I->amOnPage('/forum.pm.list/');
        $I->dontSeeElement('tr.pm-thread-unread');
        $I->dontSeeElement('.pm-unread-count');
    }

    /**
     * A reply is unread for the others; the errors of the service are shown with the username.
     */
    public function aReplyAndTheErrorsOfTheForms(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $thread = $this->thread($I, $author, [$anna]);

        $I->login($anna->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.pm.thread/' . $thread->uuid->toString());
        $I->submitForm('#frm-replyForm', ['text' => 'Sure, *see you*']);
        $I->see('see you', '.pm-message-text em');
        $I->assertFalse($this->participant($I, $author, $thread)->isRead);
        $I->assertTrue($this->participant($I, $anna, $thread)->isRead);

        $I->amOnPage('/forum.pm.thread/' . $thread->uuid->toString());
        $I->submitForm('#frm-replyForm', ['text' => 'Too fast']);
        $I->see('Píšete příliš rychle');

        $I->amOnPage('/forum.pm.create/');
        $I->submitForm('#frm-createForm', ['recipients' => 'nobody-at-all', 'subject' => 'S', 'text' => 'Hello there']);
        $I->see('Uživatel „nobody-at-all“ neexistuje nebo není aktivní.');
        $I->submitForm('#frm-createForm', ['recipients' => $anna->username, 'subject' => 'S', 'text' => 'Hello there']);
        $I->see('Sobě zprávu poslat nelze.');
    }

    /**
     * Only active participants: anyone else, one who left and an unknown uuid get 404.
     */
    public function onlyParticipantsSeeTheThread(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $outsider = $I->createUser('Outsider');
        $thread = $this->thread($I, $author, [$anna]);
        $url = '/forum.pm.thread/' . $thread->uuid->toString();

        $I->amOnPage($url);
        $I->seeElement('#frm-loginForm');

        $I->login($outsider->username, IntegrationTester::PASSWORD);
        $I->seePageIsRefused($url, 404);
        $I->seePageIsRefused('/forum.pm.thread/00000000-0000-4000-8000-000000000000', 404);
        $I->amOnPage('/forum.pm.list/');
        $I->dontSee($thread->subject);

        $I->amOnPage('/web.user.logout/');
        $I->login($anna->username, IntegrationTester::PASSWORD);
        $I->amOnPage($url);
        $I->submitForm('#frm-leaveForm', []);
        $I->seeInCurrentUrl('/forum.pm.list/');
        $I->dontSee($thread->subject);
        $I->seePageIsRefused($url, 404);
    }

    /**
     * Star, mark unread, mails and leaving only with the token; mark unread goes back to the list (the thread would read it).
     */
    public function theButtonsNeedTheToken(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $thread = $this->thread($I, $author, [$anna]);
        $url = '/forum.pm.thread/' . $thread->uuid->toString();

        $I->login($anna->username, IntegrationTester::PASSWORD);
        $I->amOnPage($url . '?do=starForm-submit');
        $I->amOnPage($url);
        $I->submitForm('#frm-starForm', ['_token_' => 'forged']);
        $I->submitForm('#frm-leaveForm', ['_token_' => 'forged']);
        $I->assertFalse($this->participant($I, $anna, $thread)->isStarred);
        $I->assertFalse($this->participant($I, $anna, $thread)->isDeleted);

        $I->amOnPage($url);
        $I->submitForm('#frm-starForm', []);
        $I->assertTrue($this->participant($I, $anna, $thread)->isStarred);
        $I->seeElement('h1 .pm-starred');

        $I->submitForm('#frm-mailForm', []);
        $I->assertFalse($this->participant($I, $anna, $thread)->wantsMail);
        $I->seeElement('.pm-mail-form[data-mail=no]');

        $I->submitForm('#frm-unreadForm', []);
        $I->seeInCurrentUrl('/forum.pm.list/');
        $I->seeElement('tr.pm-thread-unread .pm-starred');
        $I->assertFalse($this->participant($I, $anna, $thread)->isRead);
    }

    /**
     * Only the founder adds participants (the form, and the service for a POST of anyone else).
     */
    public function theFounderAddsParticipants(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');
        $thread = $this->thread($I, $author, [$anna]);
        $url = '/forum.pm.thread/' . $thread->uuid->toString();

        $I->login($anna->username, IntegrationTester::PASSWORD);
        $I->amOnPage($url);
        $I->dontSeeElement('#frm-addParticipantsForm');

        $I->amOnPage('/web.user.logout/');
        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage($url);
        $I->submitForm('#frm-addParticipantsForm', ['usernames' => $anna->username]);
        $I->see('už je účastníkem konverzace');
        $I->submitForm('#frm-addParticipantsForm', ['usernames' => mb_strtoupper($bob->username)]);
        $I->seeNumberOfElements('.pm-participant', 3);
        $I->assertNotNull($I->grabService(PrivateMessageService::class)->participantOf($bob, $thread));
    }

    /**
     * @param list<UserEntity> $recipients
     */
    private function thread(IntegrationTester $I, UserEntity $author, array $recipients) : PrivateMessageThreadEntity
    {
        $thread = $I->grabService(PrivateMessageService::class)->createThread(
            $author,
            'Subject ' . random_int(100000, 999999),
            array_map(static fn (UserEntity $user) : string => $user->username, $recipients),
            'The first message',
        );

        // older than the flood interval
        foreach ($I->em()->getRepository(PrivateMessageEntity::class)->findBy(['thread' => $thread]) as $message) {
            $message->createdAt = $message->createdAt->modify('-1 hour');
        }

        $I->em()->flush();

        return $thread;
    }

    private function participant(IntegrationTester $I, UserEntity $user, PrivateMessageThreadEntity $thread) : PrivateMessageParticipantEntity
    {
        $I->em()->clear();
        $participant = $I->em()->getRepository(PrivateMessageParticipantEntity::class)->findOneBy(['user' => $user->id, 'thread' => $thread->id]);
        $I->assertNotNull($participant);

        return $participant;
    }
}
