<?php declare(strict_types=1);

namespace App\UI\Web\User\Logout;

use App\Core\AutoLoginService;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

class LogoutPresenter extends Presenter
{
    public function __construct(
        private readonly Translator       $translator,
        private readonly AutoLoginService $autoLoginService,
    )
    {
        parent::__construct();
    }

    public function actionDefault() : void
    {
        // logging out somebody who is logged out is no error (a double click, a second tab, the back button)
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        // the "stay logged in" token goes from the database and the cookie from the browser
        $this->autoLoginService->forget($this);

        $this->getUser()->logout(true);
        $this->flashMessage($this->translator->translate('web-user-logout.success'), 'success');
        $this->redirect(':Web:User:Login:default');
    }
}
