<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\PrivateMessageEntity;
use App\Model\Entity\PrivateMessageParticipantEntity;
use App\Model\Entity\PrivateMessageThreadEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PrivateMessageException;
use App\Model\Service\PrivateMessageService as S;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Private messages in threads (slice 5b): recipients by username, participants, leaving, read / starred state, unread count,
 * text length and flood. Every test runs in a transaction that is rolled back.
 */
class PrivateMessageServiceCest
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
     * A group thread: the author and the recipients (case-insensitive usernames, each once), the first message unread only for the
     * recipients, the founder is the author.
     */
    public function aThreadIsCreatedForTheRecipients(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');

        $thread = $this->service($I)->createThread($author, '  Meeting  ', [mb_strtoupper($anna->username), $bob->username, $anna->username], 'Hello **all**');

        $I->assertSame('Meeting', $thread->subject);
        $I->assertSame($author->id, $thread->user?->id);
        $I->assertSame(1, $this->service($I)->unreadCount($anna));
        $I->assertSame(1, $this->service($I)->unreadCount($bob));
        $I->assertSame(0, $this->service($I)->unreadCount($author));
        $I->assertCount(3, $this->service($I)->activeParticipants($thread));
        $messages = $this->service($I)->messagesOf($thread);
        $I->assertCount(1, $messages);
        $I->assertSame('Hello **all**', $messages[0]->text);
    }

    public function theRecipientsAreChecked(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $inactive = $I->createUser('Inactive', active: false);
        $anna = $I->createUser('Anna');

        $this->expectError($I, S::ERROR_UNKNOWN_RECIPIENT, fn () => $this->service($I)->createThread($author, 'S', ['nobody-' . random_int(1, 999999)], 'Hello there'));
        $this->expectError($I, S::ERROR_UNKNOWN_RECIPIENT, fn () => $this->service($I)->createThread($author, 'S', [$inactive->username], 'Hello there'));
        $this->expectError($I, S::ERROR_SELF, fn () => $this->service($I)->createThread($author, 'S', [mb_strtolower($author->username)], 'Hello there'));
        $this->expectError($I, S::ERROR_NO_RECIPIENT, fn () => $this->service($I)->createThread($author, 'S', [], 'Hello there'));
        $this->expectError($I, S::ERROR_SUBJECT, fn () => $this->service($I)->createThread($author, '   ', [$anna->username], 'Hello there'));
        $this->expectError($I, S::ERROR_SUBJECT, fn () => $this->service($I)->createThread($author, str_repeat('č', 1025), [$anna->username], 'Hello there'));
        $I->assertSame(0, $this->service($I)->countThreadsOf($anna));

        $usernames = [];

        for ($i = 0; $i < S::MAX_PARTICIPANTS; $i++) {
            $usernames[] = $I->createUser('Many')->username;
        }

        $this->expectError($I, S::ERROR_TOO_MANY, fn () => $this->service($I)->createThread($author, 'S', $usernames, 'Hello there'));
        array_pop($usernames);
        $this->service($I)->createThread($author, 'S', $usernames, 'Hello there');
    }

    public function theTextIsCheckedLikeAPostAndAgainstFlood(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');

        $this->expectError($I, S::ERROR_TOO_SHORT, fn () => $this->service($I)->createThread($author, 'S', [$anna->username], ' ab '));
        $this->expectError($I, S::ERROR_TOO_LONG, fn () => $this->service($I)->createThread($author, 'S', [$anna->username], str_repeat('x', 100001)));

        $thread = $this->service($I)->createThread($author, 'S', [$anna->username], 'Hello there');
        $this->expectError($I, S::ERROR_FLOOD, fn () => $this->service($I)->reply($author, $thread, 'Again so soon'));
        $this->expectError($I, S::ERROR_FLOOD, fn () => $this->service($I)->createThread($author, 'Other', [$anna->username], 'Another thread'));

        $this->ageMessages($I, $thread);
        $this->service($I)->reply($author, $thread, 'Later is fine');
        $I->assertCount(2, $this->service($I)->messagesOf($thread));
    }

    /**
     * A reply is unread for the other active participants, read for its author; the thread goes up in the list.
     */
    public function aReplyIsUnreadForTheOthers(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');
        $older = $this->service($I)->createThread($author, 'Older', [$anna->username], 'Hello Anna');
        $this->ageMessages($I, $older);
        $thread = $this->service($I)->createThread($author, 'Newer', [$anna->username, $bob->username], 'Hello both');
        $this->ageMessages($I, $thread);
        $older->lastMessageAt = new DateTimeImmutable('-2 hours');
        $I->em()->flush();

        $this->service($I)->visited($anna, $thread);
        $this->service($I)->visited($anna, $older);
        $I->assertSame(0, $this->service($I)->unreadCount($anna));

        $this->service($I)->reply($bob, $thread, 'Reply of Bob');
        $I->assertSame(1, $this->service($I)->unreadCount($anna));
        $I->assertSame(1, $this->service($I)->unreadCount($author));
        $I->assertSame(0, $this->service($I)->unreadCount($bob), 'Who replies has read the thread.');

        $this->ageMessages($I, $thread);
        // the column has whole seconds: the reply of Bob an hour ago
        $thread->lastMessageAt = new DateTimeImmutable('-1 hour');
        $I->em()->flush();
        $I->assertSame([$thread->id, $older->id], array_map(static fn (PrivateMessageParticipantEntity $p) : int => $p->thread->id, $this->service($I)->threadsOf($anna, 0, 10)));
        $this->service($I)->reply($anna, $older, 'Reply of Anna');
        $list = $this->service($I)->threadsOf($anna, 0, 10);
        $I->assertSame([$older->id, $thread->id], array_map(static fn (PrivateMessageParticipantEntity $p) : int => $p->thread->id, $list));
    }

    /**
     * Only an active participant reads and writes; only the founder adds; one who left does not see the thread, a new message
     * does not bring it back, the founder can add him again.
     */
    public function participantsLeaveAndAreAddedOnlyByTheFounder(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');
        $outsider = $I->createUser('Outsider');
        $thread = $this->service($I)->createThread($author, 'S', [$anna->username], 'Hello Anna');
        $this->ageMessages($I, $thread);

        $I->assertNull($this->service($I)->participantOf($outsider, $thread));
        $I->assertNull($this->service($I)->participantOf(null, $thread));
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->reply($outsider, $thread, 'Let me in'));
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->setStarred($outsider, $thread, true));
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->leave($outsider, $thread));
        $this->expectError($I, S::ERROR_NOT_FOUNDER, fn () => $this->service($I)->addParticipants($anna, $thread, [$bob->username]));
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->addParticipants($outsider, $thread, [$bob->username]));
        $this->expectError($I, S::ERROR_ALREADY_PARTICIPANT, fn () => $this->service($I)->addParticipants($author, $thread, [$anna->username]));

        $this->service($I)->addParticipants($author, $thread, [$bob->username]);
        $I->assertNotNull($this->service($I)->participantOf($bob, $thread));
        $I->assertSame(1, $this->service($I)->unreadCount($bob));

        $this->service($I)->leave($anna, $thread);
        $I->assertNull($this->service($I)->participantOf($anna, $thread));
        $I->assertSame(0, $this->service($I)->countThreadsOf($anna));
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->reply($anna, $thread, 'Still here?'));
        $this->service($I)->reply($bob, $thread, 'Anna left');
        $I->assertSame(0, $this->service($I)->unreadCount($anna), 'A new message does not bring the thread back.');
        $I->assertCount(2, $this->service($I)->activeParticipants($thread));

        $this->service($I)->addParticipants($author, $thread, [$anna->username]);
        $I->assertNotNull($this->service($I)->participantOf($anna, $thread));
        $I->assertSame(1, $this->service($I)->unreadCount($anna));
        $I->assertSame(1, (int) $I->em()->getRepository(PrivateMessageParticipantEntity::class)->count(['user' => $anna, 'thread' => $thread]));

        // the founder left: nobody adds any more
        $this->service($I)->leave($author, $thread);
        $this->expectError($I, S::ERROR_NOT_PARTICIPANT, fn () => $this->service($I)->addParticipants($author, $thread, [$outsider->username]));
        $this->expectError($I, S::ERROR_NOT_FOUNDER, fn () => $this->service($I)->addParticipants($anna, $thread, [$outsider->username]));
    }

    /**
     * The read / starred / mail state is of each participant.
     */
    public function theStateIsOfEachParticipant(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $thread = $this->service($I)->createThread($author, 'S', [$anna->username], 'Hello Anna');

        $this->service($I)->setStarred($anna, $thread, true);
        $this->service($I)->setWantsMail($anna, $thread, false);
        $this->service($I)->setRead($author, $thread, false);
        $I->assertTrue($this->participant($I, $anna, $thread)->isStarred);
        $I->assertFalse($this->participant($I, $author, $thread)->isStarred);
        $I->assertFalse($this->participant($I, $anna, $thread)->wantsMail);
        $I->assertTrue($this->participant($I, $author, $thread)->wantsMail);
        $I->assertSame(1, $this->service($I)->unreadCount($author));
        $I->assertSame(1, $this->service($I)->unreadCount($anna));
        $this->service($I)->setRead($anna, $thread, true);
        $I->assertSame(0, $this->service($I)->unreadCount($anna));
    }

    public function usernamesAreParsed(IntegrationTester $I) : void
    {
        $I->assertSame(['anna', 'Bob', 'c d'], $this->service($I)->parseUsernames(" anna, Bob;\n\n c d ,"));
        $I->assertSame([], $this->service($I)->parseUsernames(' , '));
    }

    private function participant(IntegrationTester $I, UserEntity $user, PrivateMessageThreadEntity $thread) : PrivateMessageParticipantEntity
    {
        $participant = $this->service($I)->participantOf($user, $thread);
        $I->assertNotNull($participant);

        return $participant;
    }

    /**
     * The messages of the thread are older than the flood interval (posts.minUserTimeInterval = 20 s).
     */
    private function ageMessages(IntegrationTester $I, PrivateMessageThreadEntity $thread) : void
    {
        foreach ($I->em()->getRepository(PrivateMessageEntity::class)->findBy(['thread' => $thread]) as $message) {
            $message->createdAt = $message->createdAt->modify('-1 hour');
        }

        $I->em()->flush();
    }

    private function expectError(IntegrationTester $I, string $message, callable $write) : void
    {
        try {
            $write();
        } catch (PrivateMessageException $exception) {
            $I->assertSame($message, $exception->getMessage());

            return;
        }

        $I->fail('Expected the error ' . $message . '.');
    }

    private function service(IntegrationTester $I) : S
    {
        return $I->grabService(S::class);
    }
}
