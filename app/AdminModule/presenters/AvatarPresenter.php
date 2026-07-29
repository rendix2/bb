<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Repository\UserRepository;
use App\Models\UsersManager;
use App\services\AvatarService;
use App\Settings\Avatars;
use Contributte\Datagrid\Datagrid;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Utils\ArrayHash;

/**
 * Description of AvatarPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\AdminModule\Presenters
 */
class AvatarPresenter extends AdminPresenter
{
    /**
     *
     * @var Avatars $avatars
     * @inject
     */
    public Avatars $avatars;

    public function __construct(
        UsersManager $manager,
        private readonly UserRepository $userRepository,

        private readonly AvatarService $avatarService,

        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct($manager);
    }


    /**
     * @param int $page
     */
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
    
    /**
     *
     * @param int $page
     */
    public function renderDefault($page = 1): void
    {
        $this->template->avatarsSize = $this->avatars->getDirSize();
        $this->template->avatarsDir  = $this->avatars->getSPLDir()->getBasename();
    }

    /**
     * @param int    $user_id
     * @param string $avatar_name
     */
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
