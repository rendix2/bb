<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * A favourite user could not be added; the message is a translation key.
 */
class UserFavouriteException extends RuntimeException
{
}
