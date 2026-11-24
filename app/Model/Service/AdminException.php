<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * A refused change in the administration; the message is a key of the translations, with its parameters.
 */
class AdminException extends RuntimeException
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
