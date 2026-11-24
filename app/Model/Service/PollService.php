<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PollAnswerEntity;
use App\Model\Entity\PollEntity;
use App\Model\Entity\PollVoteEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * Polls of topics: one poll per topic (unique index), added by the author of an open topic or by a moderator; a user who
 * may reply votes once (unique index) until the poll ends.
 */
class PollService
{
    public const string ERROR_EXISTS = 'forum-poll.error.exists';
    public const string ERROR_QUESTION = 'forum-poll.error.question';
    public const string ERROR_ANSWERS = 'forum-poll.error.answers';
    public const string ERROR_ENDED = 'forum-poll.error.ended';
    public const string ERROR_VOTED = 'forum-poll.error.voted';
    public const string ERROR_ANSWER = 'forum-poll.error.answer';

    public const int MIN_ANSWERS = 2;
    public const int MAX_ANSWERS = 20;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
    ) {
    }

    public function pollOf(TopicEntity $topic) : ?PollEntity
    {
        return $this->em->getRepository(PollEntity::class)->findOneBy(['topic' => $topic]);
    }

    public function mayAddPoll(?UserEntity $user, TopicEntity $topic) : bool
    {
        if ($user === null) {
            return false;
        }

        return ($topic->user->id === $user->id && !$topic->isLocked)
            || $this->permissions->isAllowed($user, $topic, ForumPrivilege::MODERATE);
    }

    /**
     * @param list<string> $answers one text per answer (empty lines are skipped)
     */
    public function addPoll(UserEntity $user, TopicEntity $topic, string $question, array $answers, ?DateTimeImmutable $endsAt) : PollEntity
    {
        if (!$this->mayAddPoll($user, $topic)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        if ($this->pollOf($topic) !== null) {
            throw new PostWriteException(self::ERROR_EXISTS);
        }

        $question = Strings::trim($question);
        $answers = array_values(array_filter(array_map(static fn (string $answer) : string => Strings::trim($answer), $answers), static fn (string $answer) : bool => $answer !== ''));

        if ($question === '' || Strings::length($question) > 1024) {
            throw new PostWriteException(self::ERROR_QUESTION);
        }

        if (count($answers) < self::MIN_ANSWERS || count($answers) > self::MAX_ANSWERS) {
            throw new PostWriteException(self::ERROR_ANSWERS);
        }

        $poll = new PollEntity();
        $poll->user = $user;
        $poll->topic = $topic;
        $poll->question = $question;
        $poll->endsAt = $endsAt;

        $this->em->wrapInTransaction(function () use ($poll, $answers, $user, $topic) : void {
            $this->em->persist($poll);

            foreach ($answers as $text) {
                $answer = new PollAnswerEntity();
                $answer->poll = $poll;
                $answer->user = $user;
                $answer->topic = $topic;
                $answer->text = $text;
                $this->em->persist($answer);
                $poll->answers->add($answer);
            }
        });

        return $poll;
    }

    public function isOpen(PollEntity $poll) : bool
    {
        return $poll->endsAt === null || $poll->endsAt > new DateTimeImmutable();
    }

    public function hasVoted(?UserEntity $user, PollEntity $poll) : bool
    {
        return $user !== null && $this->em->getRepository(PollVoteEntity::class)->count(['user' => $user, 'poll' => $poll]) > 0;
    }

    public function mayVote(?UserEntity $user, PollEntity $poll) : bool
    {
        return $user !== null
            && $this->isOpen($poll)
            && $this->permissions->isAllowed($user, $poll->topic, ForumPrivilege::REPLY)
            && !$this->hasVoted($user, $poll);
    }

    public function vote(UserEntity $user, PollEntity $poll, int $answerId) : void
    {
        if (!$this->isOpen($poll)) {
            throw new PostWriteException(self::ERROR_ENDED);
        }

        if (!$this->permissions->isAllowed($user, $poll->topic, ForumPrivilege::REPLY)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        if ($this->hasVoted($user, $poll)) {
            throw new PostWriteException(self::ERROR_VOTED);
        }

        $answer = $this->em->getRepository(PollAnswerEntity::class)->findOneBy(['id' => $answerId, 'poll' => $poll]);

        if ($answer === null) {
            throw new PostWriteException(self::ERROR_ANSWER);
        }

        $vote = new PollVoteEntity();
        $vote->user = $user;
        $vote->poll = $poll;
        $vote->pollAnswer = $answer;
        $vote->topic = $poll->topic;

        $this->em->persist($vote);
        $this->em->flush();
    }

    /**
     * The answers of the poll in their order with the number of votes.
     *
     * @return list<array{answer: PollAnswerEntity, votes: int}>
     */
    public function results(PollEntity $poll) : array
    {
        $rows = $this->em->getRepository(PollVoteEntity::class)
            ->createQueryBuilder('_v')
            ->select('IDENTITY(_v.pollAnswer) AS answerId', 'COUNT(_v.id) AS votes')
            ->where('_v.poll = :poll')
            ->setParameter('poll', $poll)
            ->groupBy('_v.pollAnswer')
            ->getQuery()
            ->getArrayResult();

        $votes = [];

        foreach ($rows as $row) {
            $votes[(int) $row['answerId']] = (int) $row['votes'];
        }

        $answers = $this->em->getRepository(PollAnswerEntity::class)->findBy(['poll' => $poll], ['id' => 'ASC']);

        return array_map(static fn (PollAnswerEntity $answer) : array => ['answer' => $answer, 'votes' => $votes[$answer->id] ?? 0], $answers);
    }
}
