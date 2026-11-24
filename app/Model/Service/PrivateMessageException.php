<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * A private message was not written / changed; the message is a translation key (forum-pm.error.*) with its parameters.
 */
class PrivateMessageException extends RuntimeException
{
    /**
     * @param array<string, string|int> $parameters
     */
    public function __construct(
        string $message,
        public readonly array $parameters = [],
    ) {
        parent::__construct($message);
    }
}
