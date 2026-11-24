<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\PostHistoryEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Writing replies and topics (slice 3b): permission checked when written, length of the text, flood and double post.
 * Every test runs in a transaction that is rolled back.
 */
class PostWriteServiceCest
{
    private PostWriteService $service;

    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
        $this->service = $I->grabService(PostWriteService::class);
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function aTopicIsCreatedWithItsFirstPost(IntegrationTester $I) : void
    {
        $user = $I->createUser('Writer', role: 'Normal');
        $forum = $this->createForum($I);

        $topic = $this->service->createTopic($user, $forum, '  New topic  ', "First **post**\r\nsecond line", '127.0.0.1');

        $I->assertSame('New topic', $topic->name);
        $I->assertSame($forum->category, $topic->category);
        $posts = $I->em()->getRepository(PostEntity::class)->findBy(['topic' => $topic]);
        $I->assertCount(1, $posts);
        $I->assertSame("First **post**\nsecond line", $posts[0]->text, 'The text is trimmed and has unix new lines.');
    }

    public function aReplyIsWrittenIntoTheTopic(IntegrationTester $I) : void
    {
        $user = $I->createUser('Writer', role: 'Normal');
        $topic = $this->createTopicOf($I, $I->createUser('Author', role: 'Normal'));

        $post = $this->service->reply($user, $topic, 'A reply text', 'Re: title', '127.0.0.1');

        $I->assertSame($topic, $post->topic);
        $I->assertSame('Re: title', $post->title);
    }

    /**
     * Regression: the old code checked the permission only when the form was shown, a direct POST bypassed it.
     */
    public function thePermissionIsCheckedWhenWritten(IntegrationTester $I) : void
    {
        $user = $I->createUser('Writer', role: 'Normal');
        $topic = $this->createTopicOf($I, $I->createUser('Author', role: 'Normal'));
        $topic->isLocked = true;
        $topic->forum->permissions->topicAdd = false;
        $I->em()->flush();

        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $this->service->reply($user, $topic, 'A reply text', null, '127.0.0.1'));
        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $this->service->reply($user, $topic, 'A reply text', null, '127.0.0.1', fastReply: true));
        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $this->service->createTopic($user, $topic->forum, 'Name', 'A topic text', '127.0.0.1'));
    }

    /**
     * Regression: posts.minLength / maxLength of the configuration were never used.
     */
    public function theLengthOfTheTextIsChecked(IntegrationTester $I) : void
    {
        $user = $I->createUser('Writer', role: 'Normal');
        $topic = $this->createTopicOf($I, $I->createUser('Author', role: 'Normal'));

        // posts.minLength = 5, maxLength = 100000 (config/common.neon)
        $this->expectError($I, PostWriteService::ERROR_TOO_SHORT, fn () => $this->service->reply($user, $topic, '   abc   ', null, '127.0.0.1'));
        $this->expectError($I, PostWriteService::ERROR_TOO_LONG, fn () => $this->service->reply($user, $topic, str_repeat('x', 100001), null, '127.0.0.1'));
        $this->expectError($I, PostWriteService::ERROR_TOPIC_NAME, fn () => $this->service->createTopic($user, $topic->forum, '   ', 'A topic text', '127.0.0.1'));
    }

    public function aUserCannotWriteTooFastNorTheSamePostTwice(IntegrationTester $I) : void
    {
        $user = $I->createUser('Writer', role: 'Normal');
        $topic = $this->createTopicOf($I, $I->createUser('Author', role: 'Normal'));

        $first = $this->service->reply($user, $topic, 'The same text', null, '127.0.0.1');

        // posts.minUserTimeInterval = 20 s
        $this->expectError($I, PostWriteService::ERROR_FLOOD, fn () => $this->service->reply($user, $topic, 'Another text', null, '127.0.0.1'));

        // after the flood interval, but within posts.minDoublePostTimeInterval = 300 s
        $first->createdAt = new DateTimeImmutable('-60 seconds');
        $I->em()->flush();
        $this->expectError($I, PostWriteService::ERROR_DOUBLE_POST, fn () => $this->service->reply($user, $topic, 'The same text', null, '127.0.0.1'));

        // a different text is fine
        $I->assertInstanceOf(PostEntity::class, $this->service->reply($user, $topic, 'Another text', null, '127.0.0.1'));
    }

    public function anEditKeepsThePreviousVersionInTheHistory(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $topic = $this->createTopicOf($I, $author);
        $post = $this->service->reply($author, $topic, 'The original text', 'Original', '127.0.0.1');

        $this->service->edit($author, $post, 'Changed', 'The changed text', '10.0.0.1');

        $I->assertSame('The changed text', $post->text);
        $I->assertSame('Changed', $post->title);
        $I->assertSame('10.0.0.1', $post->editIpAddress);
        $I->assertNotNull($post->updatedAt);
        $history = $I->em()->getRepository(PostHistoryEntity::class)->findBy(['post' => $post]);
        $I->assertCount(1, $history);
        $I->assertSame('The original text', $history[0]->text);
        $I->assertSame('Original', $history[0]->title);
        $I->assertSame($author, $history[0]->user);

        // nothing changed: no new version in the history
        $this->service->edit($author, $post, 'Changed', 'The changed text', '10.0.0.1');
        $I->assertSame(1, $I->em()->getRepository(PostHistoryEntity::class)->count(['post' => $post]));
    }

    public function aLockedPostIsEditedOnlyByAModerator(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $other = $I->createUser('Other', role: 'Normal');
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $topic = $this->createTopicOf($I, $author);
        $post = $this->service->reply($author, $topic, 'The original text', null, '127.0.0.1');

        $moderatorEntity = new UserModeratorEntity();
        $moderatorEntity->user = $moderator;
        $moderatorEntity->forum = $topic->forum;
        $I->em()->persist($moderatorEntity);
        $post->isLocked = true;
        $I->em()->flush();

        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $this->service->edit($author, $post, null, 'Changed by the author', '127.0.0.1'));
        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $this->service->edit($other, $post, null, 'Changed by another user', '127.0.0.1'));

        $this->service->edit($moderator, $post, null, 'Changed by the moderator', '127.0.0.1');
        $I->assertSame('Changed by the moderator', $post->text);
    }

    private function expectError(IntegrationTester $I, string $message, callable $write) : void
    {
        try {
            $write();
        } catch (PostWriteException $exception) {
            $I->assertSame($message, $exception->getMessage());

            return;
        }

        $I->fail('Expected the error ' . $message . '.');
    }

    private function createForum(IntegrationTester $I) : ForumEntity
    {
        $category = new CategoryEntity();
        $category->name = 'Write category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $I->em()->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Write forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $I->em()->persist($forum);
        $I->em()->flush();

        return $forum;
    }

    private function createTopicOf(IntegrationTester $I, UserEntity $author) : TopicEntity
    {
        $forum = $this->createForum($I);

        $topic = new TopicEntity();
        $topic->name = 'Write topic';
        $topic->category = $forum->category ?? throw new \LogicException('No category.');
        $topic->forum = $forum;
        $topic->user = $author;
        $I->em()->persist($topic);
        $I->em()->flush();

        return $topic;
    }

}
