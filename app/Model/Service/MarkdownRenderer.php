<?php declare(strict_types=1);

namespace App\Model\Service;

use League\CommonMark\CommonMarkConverter;

/**
 * Markdown of posts, signatures and rules to HTML that is safe to print: HTML in the input is escaped (not passed through,
 * the old TinyMCE + |noescape was a stored XSS) and unsafe links (javascript:, vbscript:, file:, data: except images) are
 * dropped.
 */
class MarkdownRenderer
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }

    public function toHtml(string $markdown) : string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
