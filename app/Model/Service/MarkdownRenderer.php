<?php declare(strict_types=1);

namespace App\Model\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Markdown of posts, signatures and rules to HTML that is safe to print: HTML in the input is escaped (not passed through,
 * the old TinyMCE + |noescape was a stored XSS) and unsafe links (javascript:, vbscript:, file:, data: except images) are
 * dropped. Shortcodes of smileys (:smile:) become emoji (EmojiShortcodeParser).
 */
class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addInlineParser(new EmojiShortcodeParser());

        $this->converter = new MarkdownConverter($environment);
    }

    public function toHtml(string $markdown) : string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
