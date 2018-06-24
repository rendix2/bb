<?php

namespace App\AdminModule\Presenters;

use App\Controls\Avatars;
use App\Controls\PaginatorControl;
use App\Models\UsersManager;

/**
 * Description of AvatarPresenter
 *
 * @author rendi
 * @method UsersManager getManager()
 */
class AvatarPresenter extends Base\AdminPresenter
{
    /**
     *
     * @var Avatars $avatarsDir
     * @inject
     */
    public $avatarsDir;

    /**
     * AvatarPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        $avatars   = $this->getManager()->getAllFluent()->where('[user_avatar] IS NOT NULL');
        $paginator = new PaginatorControl($avatars, 2, 5, $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No avatars.', self::FLASH_MESSAGE_DANGER);
        }
                
        $this->template->avatarsSize = $this->avatarsDir->getDirSize();
        $this->template->avatarsDir  = $this->avatarsDir->getSPLDir()->getBasename();
        $this->template->avatars     = $avatars->fetchAll();
        $this->template->countItems  = $paginator->getCount();
    }

    /**
     * @param $user_id
     * @param $avatar_name
     */
    public function handleDeleteAvatar($user_id, $avatar_name)
    {
        \Nette\Utils\FileSystem::delete($this->avatarsDir->getDir() . DIRECTORY_SEPARATOR . $avatar_name);
        
        $this->getManager()->update($user_id, \Nette\Utils\ArrayHash::from(['user_avatar' => null]));
        
        $this->flashMessage('Avatar was deleted.', self::FLASH_MESSAGE_SUCCESS);
        
        $this->redirect('this');
    }

        //put your code here

    /**
     * @return mixed|null
     */
    protected function createComponentEditForm()
    {
        return null;
    }
}
