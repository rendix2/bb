<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Poll;

/**
 * Values of the form adding a poll to a topic (answers one per line, the end optional).
 */
final class AddPollValues
{
    public string $question;

    public string $answers;

    public ?\DateTimeImmutable $endsAt;
}
