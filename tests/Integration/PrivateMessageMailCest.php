<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\LanguageEntity;
use App\Model\Entity\MailEntity;
use App\Model\Entity\PrivateMessageEntity;
use App\Model\Entity\PrivateMessageThreadEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PrivateMessageService;
use Tests\Support\IntegrationTester;

/**
 * Mails about a new private message (slice 5d): queued for the other active participants who want them, one until the next visit
 * of the thread, in the language of the recipient. Every test runs in a transaction that is rolled back.
 */
class PrivateMessageMailCest
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

    public function oneMailUntilTheNextVisit(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');
        $service = $I->grabService(PrivateMessageService::class);

        $thread = $service->createThread($author, 'Plans', [$anna->username, $bob->username], 'The first message');
        $I->assertSame(0, $this->mailCount($I, $author));
        $I->assertSame(1, $this->mailCount($I, $anna));
        $I->assertSame(1, $this->mailCount($I, $bob));

        $this->age($I, $thread);
        $service->reply($author, $thread, 'Did you read it?');
        $I->assertSame(1, $this->mailCount($I, $anna), 'Anna did not visit the thread: no second mail.');

        $service->visited($anna, $thread);
        $this->age($I, $thread);
        $service->reply($bob, $thread, 'Not yet');
        $I->assertSame(2, $this->mailCount($I, $anna));
        $I->assertSame(1, $this->mailCount($I, $author));
        $I->assertSame(1, $this->mailCount($I, $bob));
    }

    /**
     * Not to one who turned the mails off, nor to one who left the thread.
     */
    public function onlyToParticipantsWhoWantIt(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $anna = $I->createUser('Anna');
        $bob = $I->createUser('Bob');
        $service = $I->grabService(PrivateMessageService::class);
        $thread = $service->createThread($author, 'Plans', [$anna->username, $bob->username], 'The first message');
        $service->visited($anna, $thread);
        $service->visited($bob, $thread);

        $service->setWantsMail($anna, $thread, false);
        $service->leave($bob, $thread);
        $this->age($I, $thread);
        $service->reply($author, $thread, 'Anyone?');

        $I->assertSame(1, $this->mailCount($I, $anna));
        $I->assertSame(1, $this->mailCount($I, $bob));
    }

    /**
     * The mail is in the language of each recipient, with the link to the message.
     */
    public function theMailIsInTheLanguageOfTheRecipient(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author');
        $author->language = $this->language($I, 'en');
        $english = $I->createUser('English');
        $english->language = $this->language($I, 'en');
        $czech = $I->createUser('Czech');
        $czech->language = $this->language($I, 'cs');
        $I->em()->flush();

        $thread = $I->grabService(PrivateMessageService::class)->createThread($author, 'Holiday', [$english->username, $czech->username], 'The first message');
        $message = $I->em()->getRepository(PrivateMessageEntity::class)->findOneBy(['thread' => $thread]);
        $I->assertNotNull($message);

        $englishMail = $this->lastMailTo($I, $english);
        $I->assertSame('A new private message: Holiday', $englishMail->subject);
        $I->assertStringContainsString('There is a new message from', $englishMail->body);
        $I->assertStringContainsString($author->username, $englishMail->body);
        $I->assertStringContainsString('/forum.pm.thread/' . $thread->uuid->toString() . '#message-' . $message->id, $englishMail->body);
        $I->assertStringNotContainsString('The first message', $englishMail->body);

        $czechMail = $this->lastMailTo($I, $czech);
        $I->assertSame('Nová soukromá zpráva: Holiday', $czechMail->subject);
        $I->assertStringContainsString('je nová zpráva od uživatele', $czechMail->body);
    }

    private function mailCount(IntegrationTester $I, UserEntity $user) : int
    {
        return $I->em()->getRepository(MailEntity::class)->count(['emailTo' => $user->email]);
    }

    private function lastMailTo(IntegrationTester $I, UserEntity $user) : MailEntity
    {
        $mail = $I->em()->getRepository(MailEntity::class)->findOneBy(['emailTo' => $user->email], ['id' => 'DESC']);
        $I->assertNotNull($mail);

        return $mail;
    }

    private function language(IntegrationTester $I, string $locale) : LanguageEntity
    {
        $language = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => $locale]);
        $I->assertNotNull($language);

        return $language;
    }

    /**
     * The messages are older than the flood interval.
     */
    private function age(IntegrationTester $I, PrivateMessageThreadEntity $thread) : void
    {
        foreach ($I->em()->getRepository(PrivateMessageEntity::class)->findBy(['thread' => $thread]) as $message) {
            $message->createdAt = $message->createdAt->modify('-1 hour');
        }

        $I->em()->flush();
    }
}
