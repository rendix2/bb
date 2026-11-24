<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PollEntity;
use App\Model\Entity\PollVoteEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PollService;
use App\Model\Service\PostWriteException;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Polls (slice 3k): added by the author of the topic, voted once while open. Rolled back after each test.
 */
class PollPagesCest
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

    public function theAuthorAddsAPollAndAUserVotesOnce(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $topic = $this->createTopic($I, $author);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.add-poll-link');
        $I->submitForm('#frm-addPollForm', ['question' => 'Best colour?', 'answers' => "Red\n\nBlue\n Green "]);

        $poll = $I->em()->getRepository(PollEntity::class)->findOneBy(['topic' => $topic->id]);
        $I->assertNotNull($poll);
        $I->assertCount(3, $I->grabService(PollService::class)->results($poll), 'Empty lines are skipped, answers trimmed.');

        $voter = $I->createUser('Voter', role: 'Normal');
        $I->login($voter->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->see('Best colour?', '.poll-question');
        $blue = $I->grabService(PollService::class)->results($poll)[1]['answer'];
        $I->submitForm('#frm-voteForm', ['answer' => (string) $blue->id]);

        $I->assertSame(1, $I->em()->getRepository(PollVoteEntity::class)->count(['poll' => $poll->id, 'pollAnswer' => $blue->id]));
        $I->dontSeeElement('#frm-voteForm');

        $this->expectError($I, PollService::ERROR_VOTED, fn () => $I->grabService(PollService::class)->vote($this->user($I, $voter), $this->poll($I, $poll), $blue->id));
    }

    public function anotherUserMayNotAddAPollAndAnswersAreValidated(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $topic = $this->createTopic($I, $author);
        $other = $I->createUser('Other', role: 'Normal');
        $service = $I->grabService(PollService::class);

        $I->login($other->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('.add-poll-link');
        $I->seePageIsRefused('/forum.topic.poll/' . $topic->uuid->toString(), 403);

        $this->expectError($I, PollService::ERROR_ANSWERS, fn () => $service->addPoll($author, $topic, 'Question?', ['Only one'], null));
        $this->expectError($I, PollService::ERROR_QUESTION, fn () => $service->addPoll($author, $topic, '   ', ['A', 'B'], null));
    }

    public function anEndedPollTakesNoVotes(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $topic = $this->createTopic($I, $author);
        $service = $I->grabService(PollService::class);
        $poll = $service->addPoll($author, $topic, 'Ended?', ['Yes', 'No'], new DateTimeImmutable('-1 hour'));
        $voter = $I->createUser('Voter', role: 'Normal');

        $I->assertFalse($service->mayVote($voter, $poll));
        $this->expectError($I, PollService::ERROR_ENDED, fn () => $service->vote($voter, $poll, $service->results($poll)[0]['answer']->id));
    }

    private function user(IntegrationTester $I, UserEntity $user) : UserEntity
    {
        return $I->em()->find(UserEntity::class, $user->id) ?? $user;
    }

    private function poll(IntegrationTester $I, PollEntity $poll) : PollEntity
    {
        return $I->em()->find(PollEntity::class, $poll->id) ?? $poll;
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

    private function createTopic(IntegrationTester $I, UserEntity $author) : TopicEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Poll category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Poll forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Poll topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'The first post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return $topic;
    }

}
