<?php declare(strict_types=1);

namespace App\Core\Translation;

use Contributte\Translation\Translator;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Security\User;

/**
 * Sets the language of the interface for every request (Application::onPresenter, after the cookie login of AutoLoginService) and
 * again when the user logs in or out (User::onLoggedIn / onLoggedOut, the page of the login form is answered in the same request).
 * The translator resolves the locale only once (the first getLocale()); without this a container living longer than one request
 * (the tests) or a login during the request kept the old language. Wired in config/common.neon.
 */
class UserLocaleService
{
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    public function onPresenter(Application $application, IPresenter $presenter) : void
    {
        $this->refresh();
    }

    public function onUserChanged(User $user) : void
    {
        $this->refresh();
    }

    private function refresh() : void
    {
        $this->translator->setLocale($this->translator->getLocaleResolver()->resolve($this->translator));
    }
}
