<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\MailEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\MailQueueService;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicWatchService;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Mails to the watchers of a topic (slice 3i): queued on a new reply, one per watcher until his next visit, never to the
 * author of the reply; sent by MailQueueService (the console command mail:send-queue). Rolled back after each test.
 */
class MailQueueCest
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
     * Regression: the old code put all watchers into one To: and mailed user ids instead of addresses.
     */
    public function aReplyQueuesOneMailForEachWatcherButTheAuthor(IntegrationTester $I) : void
    {
        [$topic, $author] = $this->createTopic($I);
        $watcher1 = $I->createUser('Watcher', role: 'Normal');
        $watcher2 = $I->createUser('Watcher', role: 'Normal');
        $replier = $I->createUser('Replier', role: 'Normal');
        $watch = $I->grabService(TopicWatchService::class);
        foreach ([$watcher1, $watcher2, $replier] as $user) {
            $watch->watch($user, $topic);
        }

        $I->grabService(PostWriteService::class)->reply($replier, $topic, 'A reply for the watchers', null, '127.0.0.1');

        $queued = $this->queuedTo($I, [$watcher1, $watcher2, $replier, $author]);
        $I->assertSame([$watcher1->email => 1, $watcher2->email => 1], $queued);
        $I->assertStringContainsString('/forum.topic.view/' . $topic->uuid->toString(), $this->lastMailTo($I, $watcher1)->body);
    }

    public function aWatcherGetsOneMailUntilHisNextVisit(IntegrationTester $I) : void
    {
        [$topic] = $this->createTopic($I);
        $watcher = $I->createUser('Watcher', role: 'Normal');
        $watchService = $I->grabService(TopicWatchService::class);
        $watchService->watch($watcher, $topic);
        $write = $I->grabService(PostWriteService::class);

        $replier1 = $I->createUser('Replier', role: 'Normal');
        $replier2 = $I->createUser('Replier', role: 'Normal');
        $write->reply($replier1, $topic, 'The first reply', null, '127.0.0.1');
        $write->reply($replier2, $topic, 'The second reply', null, '127.0.0.1');
        $I->assertSame([$watcher->email => 1], $this->queuedTo($I, [$watcher]));

        $watchService->visited($watcher, $topic);
        $I->em()->clear();
        $topic = $I->em()->find(TopicEntity::class, $topic->id) ?? $topic;
        $replier3 = $I->em()->find(UserEntity::class, $I->createUser('Replier', role: 'Normal')->id);
        $I->assertNotNull($replier3);
        $write->reply($replier3, $topic, 'The third reply', null, '127.0.0.1');
        $I->assertSame([$watcher->email => 2], $this->queuedTo($I, [$watcher]));
    }

    public function theQueueIsSentAndMarked(IntegrationTester $I) : void
    {
        $queue = $I->grabService(MailQueueService::class);
        // older queued mails of the dev database must not be sent by the test: mark them as sent within the transaction
        $I->em()->getRepository(MailEntity::class)->createQueryBuilder('_m')->update()->set('_m.sentAt', ':now')->where('_m.sentAt IS NULL')->setParameter('now', new DateTimeImmutable())->getQuery()->execute();

        $mail = $queue->enqueue('queue-test@example.com', 'Subject', '<p>Body</p>');
        $I->em()->flush();

        $I->assertSame(1, $queue->sendQueued(10));
        $I->assertNotNull($mail->sentAt);
        $I->assertSame(0, $queue->sendQueued(10));
    }

    /**
     * @param list<UserEntity> $users
     * @return array<string, int> e-mail => number of queued mails, only the addresses that have some
     */
    private function queuedTo(IntegrationTester $I, array $users) : array
    {
        $counts = [];

        foreach ($users as $user) {
            $count = $I->em()->getRepository(MailEntity::class)->count(['emailTo' => $user->email, 'sentAt' => null]);

            if ($count > 0) {
                $counts[$user->email] = $count;
            }
        }

        return $counts;
    }

    private function lastMailTo(IntegrationTester $I, UserEntity $user) : MailEntity
    {
        $mail = $I->em()->getRepository(MailEntity::class)->findOneBy(['emailTo' => $user->email], ['id' => 'DESC']);
        $I->assertNotNull($mail);

        return $mail;
    }

    /**
     * @return array{TopicEntity, UserEntity}
     */
    private function createTopic(IntegrationTester $I) : array
    {
        $em = $I->em();
        $author = $I->createUser('Author', role: 'Normal');

        $category = new CategoryEntity();
        $category->name = 'Mail category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Mail forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Mail topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->category = $category;
        $post->forum = $forum;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'The first post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $author];
    }

}
