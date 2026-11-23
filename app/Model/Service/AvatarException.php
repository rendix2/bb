<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * An avatar was not stored; the message is a translation key (web-user-edit.avatar.error.*).
 */
class AvatarException extends RuntimeException
{
}
