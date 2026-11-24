<?php declare(strict_types=1);

namespace App\UI\Admin;

use App\Core\AutoLoginService;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Nette\Http\IResponse;
use stdClass;

/**
 * class AdminBasePresenter
 *
 * @package App\UI\Admin
 */
abstract class AdminBasePresenter extends Presenter
{
    #[Inject]
    public AutoLoginService $autoLoginService;

    // bb: admin()
    #[Inject]
    public EntityManagerDecorator $adminEntityManager;

    public function startup() : void
    {
        parent::startup();

        // the admin has to be logged in: the "stay logged in" cookie is the last chance, otherwise 401
        if (!$this->getUser()->isLoggedIn() && !$this->autoLoginService->login($this)) {
            $this->error('Not logged in', IResponse::S401_Unauthorized);
        }

        if (!$this->getUser()->isInRole('Admin')) {
            $this->error('Forbidden', IResponse::S403_Forbidden);
        }
    }

    /**
     * bb: the logged-in admin as an entity (the author of a ban etc.).
     */
    protected function admin() : UserEntity
    {
        return $this->adminEntityManager->find(UserEntity::class, $this->getUser()->getId())
            ?? $this->error('Not logged in', IResponse::S401_Unauthorized);
    }

    /**
     * bb: a flash message for Admin/@layout.latte (an object with place, type, message, as web-project builds it inline).
     */
    protected function adminFlash(string $message, string $type = 'success') : void
    {
        $flash = new stdClass();
        $flash->place = 'general';
        $flash->type = $type;
        $flash->message = $message;

        $this->flashMessage($flash);
    }
}
