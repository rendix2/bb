<?php

namespace App\AdminModule\Presenters;

use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Repository\UserRepository;
use App\Models\UsersManager;
use App\services\AvatarService;
use App\Settings\Avatars;
use Contributte\Datagrid\Datagrid;
use Contributte\FormsBootstrap\BootstrapForm;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of AvatarPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\AdminModule\Presenters
 */
class AvatarPresenter extends Presenter
{

    public function __construct(
        UsersManager $manager,
        private readonly UserRepository $userRepository,

        private readonly AvatarService $avatarService,

        private readonly EntityManagerDecorator $em,

        private readonly Avatars $avatars,
    )
    {
        parent::__construct();
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();

        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);

        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     * @param int $id
     */
    public function actionDelete(int $id)
    {
        if (!is_numeric($id)) {
            $this->error('Parameter is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', 'success');
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    public function renderEdit(int $id): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this['editForm']->setDefaults([]);
        }
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->getManager()->update($id, $values);
            } else {
                $result = $id = $this->getManager()->add($values);
            }

            if ($result) {
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Nothing to save.', self::FLASH_MESSAGE_INFO);
            }
        } catch (DriverException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    public function actionDefault($page = 1): void
    {
        $avatars = $this->userRepository->findWithAvatar();
        $paginator = new PaginatorControl($avatars, 2, 5, $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No avatars.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->avatars     = $avatars;
        $this->template->countItems  = $paginator->getCount();
    }
    
    public function renderDefault($page = 1): void
    {
        $this->template->avatarsSize = $this->avatars->getDirSize();
        $this->template->avatarsDir  = $this->avatars->getSPLDir()->getBasename();
    }

    public function handleDeleteAvatar(int $user_id, string $avatar_name): void
    {
        $this->avatarService->removeAvatarFile($avatar_name);

        $userEntity = $this->userRepository->findOneBy(
            [
                'id' => $user_id,
            ]
        );

        $userEntity->avatar = null;
        
        $this->em->persist($userEntity);
        $this->em->flush();
        
        $this->flashMessage('Avatar was deleted.', self::FLASH_MESSAGE_SUCCESS);
        
        $this->redirect('this');
    }

    protected function createComponentEditForm(): BootstrapForm
    {
        $form = new BootstrapForm();

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }
    
    protected function createComponentDataGrid(): Datagrid
    {
        $datagrid = new Datagrid();

        return $datagrid;
    }
}
