<?php declare(strict_types=1);

namespace App\Core;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Service\BanService;
use App\UI\Web\Banned\BannedPresenter;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;

/**
 * Enforces the bans of the whole web and of the login on every request (Application::onPresenter, after the cookie login of
 * AutoLoginService): a banned account is logged out, a visitor banned from the whole web (also a guest by his IP address)
 * sees only Web:Banned. The redirect waits for the startup of the presenter (onStartup), a redirect in onPresenter would end
 * on the error page.
 */
class BanGuard
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly BanService $banService,
    ) {
    }

    public function onPresenter(Application $application, IPresenter $presenter) : void
    {
        if (!$presenter instanceof Presenter || $presenter instanceof BannedPresenter || str_starts_with($presenter::class, 'App\\UI\\Error\\')) {
            return;
        }

        $ipAddress = $presenter->getHttpRequest()->getRemoteAddress();
        $user = $presenter->getUser()->isLoggedIn() ? $this->em->find(UserEntity::class, $presenter->getUser()->getId()) : null;
        // a logged-in account: banned from the login or the whole web; a guest: only the whole web by his IP address
        $ban = $this->banService->find($user, null, $ipAddress, $user !== null ? BanScope::Login : BanScope::Web);

        if ($ban === null) {
            return;
        }

        if ($user !== null) {
            $presenter->getUser()->logout(true);
        }

        $presenter->onStartup[] = function (Presenter $presenter) use ($ban) : void {
            $presenter->redirect(':Web:Banned:default', ['uuid' => $ban->uuid->toString()]);
        };
    }
}
