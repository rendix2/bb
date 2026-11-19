<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Create;

/**
 * Values of the form of a new topic (its name and the text of its first post).
 */
final class CreateTopicValues
{
    public string $name;

    public string $text;

    /** @var list<\Nette\Http\FileUpload> */
    public array $attachments;
}
