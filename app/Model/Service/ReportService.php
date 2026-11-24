<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Settings\SiteUrl;
use Contributte\Translation\Translator;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\LinkGenerator;
use Nette\Utils\Strings;

/**
 * A logged-in user reports a post he can see (not his own) to the moderators, once per post (also guarded by the unique
 * index of the table report). The moderators of the forum of the post (and the admin) handle it (slice 6): resolved or
 * rejected with a note only for the moderators; the reporter gets a private message from the moderator in his own language.
 */
class ReportService
{
    public const int STATUS_OPEN = 0;
    public const int STATUS_RESOLVED = 1;
    public const int STATUS_REJECTED = 2;

    public const int TEXT_MAX_LENGTH = 5000;

    public const string ERROR_OWN = 'forum-report.error.own';
    public const string ERROR_ALREADY = 'forum-report.error.already';
    public const string ERROR_EMPTY = 'forum-report.error.empty';
    public const string ERROR_TOO_LONG = 'forum-report.error.tooLong';
    public const string ERROR_HANDLED = 'forum-report.error.handled';
    public const string ERROR_STATUS = 'forum-report.error.status';
    public const string ERROR_NOTE_TOO_LONG = 'forum-report.error.noteTooLong';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PrivateMessageService $privateMessages,
        private readonly Translator $translator,
        private readonly LinkGenerator $linkGenerator,
        private readonly SiteUrl $siteUrl,
    ) {
    }

    /**
     * May the user report the post at all (the link and the page; "once" is checked separately)?
     */
    public function mayReport(?UserEntity $user, PostEntity $post) : bool
    {
        return $user !== null
            && $post->user->id !== $user->id
            && $this->permissions->isAllowed($user, $post, ForumPrivilege::VIEW);
    }

    public function hasReported(?UserEntity $user, PostEntity $post) : bool
    {
        return $user !== null && $this->em->getRepository(ReportEntity::class)->count(['user' => $user, 'post' => $post]) > 0;
    }

    public function report(UserEntity $user, PostEntity $post, string $text, string $ipAddress) : ReportEntity
    {
        if (!$this->permissions->isAllowed($user, $post, ForumPrivilege::VIEW)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        if ($post->user->id === $user->id) {
            throw new PostWriteException(self::ERROR_OWN);
        }

        $text = Strings::trim($text);

        if ($text === '') {
            throw new PostWriteException(self::ERROR_EMPTY);
        }

        if (Strings::length($text) > self::TEXT_MAX_LENGTH) {
            throw new PostWriteException(self::ERROR_TOO_LONG);
        }

        if ($this->hasReported($user, $post)) {
            throw new PostWriteException(self::ERROR_ALREADY);
        }

        $report = new ReportEntity();
        $report->user = $user;
        $report->post = $post;
        $report->topic = $post->topic;
        $report->forum = $post->forum;
        $report->category = $post->category;
        $report->reportText = $text;
        $report->status = self::STATUS_OPEN;
        $report->ipAddress = $ipAddress;

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }

    /**
     * May the user handle the report (a moderator of the forum of the reported post, the admin)?
     */
    public function mayHandle(?UserEntity $user, ReportEntity $report) : bool
    {
        return $user !== null && $this->permissions->isAllowed($user, $report->forum, ForumPrivilege::MODERATE);
    }

    /**
     * Does the user moderate any forum (the link to the reports in the layout, the page)?
     */
    public function isModerator(UserEntity $user) : bool
    {
        return $this->permissions->moderatedForumIds($user) !== [];
    }

    /**
     * Open (or handled) reports of the forums the moderator moderates, the newest first (handled: the last handled first).
     *
     * @return list<ReportEntity>
     */
    public function reportsFor(UserEntity $moderator, bool $open, int $offset, int $limit) : array
    {
        $query = $this->reportsQuery($moderator, $open);

        if ($query === null) {
            return [];
        }

        /** @var list<ReportEntity> */
        return $query
            ->select('_r', '_p', '_t', '_f', '_u')
            ->innerJoin('_r.post', '_p')
            ->innerJoin('_r.topic', '_t')
            ->innerJoin('_r.forum', '_f')
            ->innerJoin('_r.user', '_u')
            ->orderBy($open ? '_r.createdAt' : '_r.handledAt', 'DESC')
            ->addOrderBy('_r.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countFor(UserEntity $moderator, bool $open) : int
    {
        $query = $this->reportsQuery($moderator, $open);

        return $query === null ? 0 : (int) $query->select('COUNT(_r.id)')->getQuery()->getSingleScalarResult();
    }

    /**
     * The moderator resolves or rejects an open report (with a note for the other moderators); the reporter gets a private
     * message from him with the result, in the language of the reporter.
     */
    public function handle(UserEntity $moderator, ReportEntity $report, int $status, ?string $note) : void
    {
        if (!$this->mayHandle($moderator, $report)) {
            throw new PostWriteException(PostWriteService::ERROR_NOT_ALLOWED);
        }

        if (!in_array($status, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true)) {
            throw new PostWriteException(self::ERROR_STATUS);
        }

        if ($report->status !== self::STATUS_OPEN) {
            throw new PostWriteException(self::ERROR_HANDLED);
        }

        $note = $note !== null ? Strings::trim($note) : '';

        if (Strings::length($note) > self::TEXT_MAX_LENGTH) {
            throw new PostWriteException(self::ERROR_NOTE_TOO_LONG);
        }

        $report->status = $status;
        $report->handledBy = $moderator;
        $report->handledAt = new DateTimeImmutable();
        $report->moderatorNote = $note !== '' ? $note : null;
        $report->updatedAt = $report->handledAt;
        $this->em->flush();

        $this->notifyReporter($moderator, $report);
    }

    private function notifyReporter(UserEntity $moderator, ReportEntity $report) : void
    {
        $reporter = $report->user;
        $locale = $reporter->language->locale;
        $result = $report->status === self::STATUS_RESOLVED ? 'resolved' : 'rejected';
        // against the URL of the web, not of the current request
        $url = $this->linkGenerator->withReferenceUrl((string) $this->siteUrl->get())->link('Forum:Topic:View:default', [
            'uuid' => $report->topic->uuid->toString(),
        ]);
        $parameters = [
            'topic' => $report->topic->name,
            'url' => $url,
            'date' => $report->createdAt->format('j. n. Y H:i'),
        ];

        $this->privateMessages->createAutomaticThread(
            $moderator,
            $reporter,
            $this->translator->translate('forum-report.message.subject', $parameters, null, $locale),
            $this->translator->translate('forum-report.message.' . $result, $parameters, null, $locale),
        );
    }

    /**
     * One query for the list and its count; null when the user moderates no forum.
     */
    private function reportsQuery(UserEntity $moderator, bool $open) : ?QueryBuilder
    {
        $forumIds = $this->permissions->moderatedForumIds($moderator);

        if ($forumIds === []) {
            return null;
        }

        return $this->em->getRepository(ReportEntity::class)
            ->createQueryBuilder('_r')
            ->where('_r.forum IN (:forums)')
            ->andWhere($open ? '_r.status = :open' : '_r.status != :open')
            ->setParameter('forums', $forumIds)
            ->setParameter('open', self::STATUS_OPEN);
    }
}
