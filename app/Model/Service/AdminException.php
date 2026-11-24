<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * A refused change in the administration; the message is a key of the translations.
 */
class AdminException extends RuntimeException
{
}
