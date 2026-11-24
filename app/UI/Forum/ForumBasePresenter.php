<?php declare(strict_types=1);

namespace App\UI\Forum;

use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Nette\Localization\Translator;
use stdClass;

/**
 * class ForumBasePresenter
 *
 * The forum pages that send flash messages (Forum/@layout.latte prints them already translated).
 *
 * @package App\UI\Forum
 */
abstract class ForumBasePresenter extends Presenter
{
    #[Inject]
    public Translator $forumTranslator;

    /**
     * A flash message as an object with place, type and message (as web-project builds it), translated here.
     *
     * @param string                    $key        the key of the translation (also the key of a PostWriteException etc.)
     * @param array<string, string|int> $parameters
     */
    protected function forumFlash(string $key, string $type = 'success', array $parameters = []) : void
    {
        $flash = new stdClass();
        $flash->place = 'general';
        $flash->type = $type;
        $flash->message = $this->forumTranslator->translate($key, $parameters);

        $this->flashMessage($flash);
    }
}
