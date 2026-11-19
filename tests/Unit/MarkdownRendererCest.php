<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Service\MarkdownRenderer;
use Tests\Support\UnitTester;

/**
 * Markdown of posts is printed as HTML: it must be safe (the old TinyMCE HTML + |noescape was a stored XSS).
 */
class MarkdownRendererCest
{
    public function markdownBecomesHtml(UnitTester $I) : void
    {
        $html = (new MarkdownRenderer())->toHtml("**bold** and *italic*\n\n- item\n\n[link](https://example.com)");

        $I->assertStringContainsString('<strong>bold</strong>', $html);
        $I->assertStringContainsString('<em>italic</em>', $html);
        $I->assertStringContainsString('<li>item</li>', $html);
        $I->assertStringContainsString('<a href="https://example.com">link</a>', $html);
    }

    public function htmlInTheInputIsEscaped(UnitTester $I) : void
    {
        $html = (new MarkdownRenderer())->toHtml("<script>alert(1)</script>\n\n<img src=x onerror=alert(2)>");

        $I->assertStringNotContainsString('<script', $html);
        $I->assertStringNotContainsString('<img', $html);
        $I->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function unsafeLinksAreDropped(UnitTester $I) : void
    {
        $html = (new MarkdownRenderer())->toHtml('[click](javascript:alert(1)) ![x](javascript:alert(2))');

        $I->assertStringNotContainsString('javascript:', $html);
    }
}
