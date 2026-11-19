<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use Nette\Utils\Strings;

/**
 * A logged-in user reports a post he can see (not his own) to the moderators, once per post (also guarded by the unique
 * index of the table report). The moderators handle the reports later (slice 6).
 */
class ReportService
{
    public const int STATUS_OPEN = 0;

    public const int TEXT_MAX_LENGTH = 5000;

    public const string ERROR_OWN = 'forum-report.error.own';
    public const string ERROR_ALREADY = 'forum-report.error.already';
    public const string ERROR_EMPTY = 'forum-report.error.empty';
    public const string ERROR_TOO_LONG = 'forum-report.error.tooLong';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
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
}
