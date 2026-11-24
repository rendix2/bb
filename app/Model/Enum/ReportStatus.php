<?php declare(strict_types=1);

namespace App\Model\Enum;

/**
 * The state of a post report (report.status); a moderator resolves or rejects an open report once.
 */
enum ReportStatus: string
{
    case Open = 'open';

    case Resolved = 'resolved';

    case Rejected = 'rejected';

    /**
     * Is it a result a moderator chooses from (not open)?
     */
    public function isResult() : bool
    {
        return $this !== self::Open;
    }

    /**
     * The key of the translation (forum-moderation-reports.status.<value>).
     */
    public function translationKey() : string
    {
        return 'forum-moderation-reports.status.' . $this->value;
    }
}
