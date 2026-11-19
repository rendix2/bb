<?php declare(strict_types=1);

namespace App\UI\Forum\Topic\Reply;

/**
 * Values of the reply form (title is optional).
 */
final class ReplyValues
{
    public string $title;

    public string $text;

    /** @var list<\Nette\Http\FileUpload> */
    public array $attachments;
}
