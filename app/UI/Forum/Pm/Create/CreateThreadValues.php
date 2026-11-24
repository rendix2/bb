<?php declare(strict_types=1);

namespace App\UI\Forum\Pm\Create;

/**
 * Values of the form of a new private message thread.
 */
final class CreateThreadValues
{
    public string $recipients;

    public string $subject;

    public string $text;
}
