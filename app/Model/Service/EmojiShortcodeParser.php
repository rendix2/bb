<?php declare(strict_types=1);

namespace App\Model\Service;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * The smileys of posts (slice 7e3, instead of the old administration of smileys): an emoji typed directly passes as it is,
 * a shortcode :smile: of the fixed map becomes its emoji. An inline parser, so code spans, code blocks and URLs keep the
 * text; an unknown :name: stays as it was written.
 */
class EmojiShortcodeParser implements InlineParserInterface
{
    public const array Emoji = [
        'smile' => '😄',
        'smiley' => '😃',
        'grin' => '😁',
        'laughing' => '😆',
        'joy' => '😂',
        'rofl' => '🤣',
        'sweat_smile' => '😅',
        'slight_smile' => '🙂',
        'upside_down_face' => '🙃',
        'wink' => '😉',
        'blush' => '😊',
        'innocent' => '😇',
        'heart_eyes' => '😍',
        'kissing_heart' => '😘',
        'stuck_out_tongue' => '😛',
        'stuck_out_tongue_winking_eye' => '😜',
        'sunglasses' => '😎',
        'nerd_face' => '🤓',
        'smirk' => '😏',
        'thinking' => '🤔',
        'roll_eyes' => '🙄',
        'neutral_face' => '😐',
        'expressionless' => '😑',
        'confused' => '😕',
        'worried' => '😟',
        'disappointed' => '😞',
        'cry' => '😢',
        'sob' => '😭',
        'angry' => '😠',
        'rage' => '😡',
        'open_mouth' => '😮',
        'astonished' => '😲',
        'scream' => '😱',
        'flushed' => '😳',
        'sleeping' => '😴',
        'facepalm' => '🤦',
        'shrug' => '🤷',
        'eyes' => '👀',
        '+1' => '👍',
        'thumbsup' => '👍',
        '-1' => '👎',
        'thumbsdown' => '👎',
        'clap' => '👏',
        'wave' => '👋',
        'ok_hand' => '👌',
        'pray' => '🙏',
        'muscle' => '💪',
        'heart' => '❤️',
        'broken_heart' => '💔',
        'fire' => '🔥',
        'star' => '⭐',
        'tada' => '🎉',
        '100' => '💯',
        'coffee' => '☕',
        'beer' => '🍺',
        'warning' => '⚠️',
        'question' => '❓',
        'exclamation' => '❗',
        'white_check_mark' => '✅',
        'x' => '❌',
    ];

    public function getMatchDefinition() : InlineParserMatch
    {
        return InlineParserMatch::regex(':([a-z0-9_+-]+):');
    }

    public function parse(InlineParserContext $inlineContext) : bool
    {
        $name = strtolower($inlineContext->getSubMatches()[0]);

        if (!isset(self::Emoji[$name])) {
            return false;
        }

        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(new Text(self::Emoji[$name]));

        return true;
    }
}
