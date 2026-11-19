<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * A post or topic was not written; the message is a translation key (forum-post-write.error.*).
 */
class PostWriteException extends RuntimeException
{
}
