<?php

namespace App\AdminModule\Presenters;

use App\Controls\Avatars;
use App\Controls\PaginatorControl;
use App\Models\UsersManager;
use Nette\Utils\ArrayHash;

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
     * @var Avatars $avatars
     * @inject
     */
    public $avatars;

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
                
        $this->template->avatarsSize = $this->avatars->getDirSize();
        $this->template->avatarsDir  = $this->avatars->getSPLDir()->getBasename();
        $this->template->avatars     = $avatars->fetchAll();
        $this->template->countItems  = $paginator->getCount();
    }

    /**
     * @param int    $user_id
     * @param string $avatar_name
     */
    public function handleDeleteAvatar($user_id, $avatar_name)
    {        
        $this->getManager()->removeAvatarFile($avatar_name);
        
        $this->getManager()->update($user_id, ArrayHash::from(['user_avatar' => null]));
        
        $this->flashMessage('Avatar was deleted.', self::FLASH_MESSAGE_SUCCESS);
        
        $this->redirect('this');
    }

    /**
     * @return null
     */
    protected function createComponentEditForm()
    {
        return null;
    }
}
