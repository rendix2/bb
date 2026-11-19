<?php declare(strict_types=1);

namespace App\UI\Accessory;

use App\Model\Service\MarkdownRenderer;
use Latte\Extension;
use Latte\Runtime\Html;

/**
 * Filters of the application: {$post->text|markdown} prints the Markdown as safe HTML (MarkdownRenderer).
 */
class LatteExtension extends Extension
{
    public function __construct(
        private readonly MarkdownRenderer $markdownRenderer,
    ) {
    }

    /**
     * @return array<string, callable>
     */
    public function getFilters() : array
    {
        return [
            'markdown' => fn (?string $markdown) : Html => new Html($this->markdownRenderer->toHtml((string) $markdown)),
        ];
    }
}
