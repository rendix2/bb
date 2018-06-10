<?php

namespace App\AdminModule\Presenters;

use App\Controls\PaginatorControl;

/**
 * Description of AvatarPresenter
 *
 * @author rendi
 * @method \App\Models\UsersManager getManager()
 */
class AvatarPresenter extends Base\AdminPresenter
{
    /**
     *
     * @var \App\Controls\Avatars $avatarsDir
     */
    private $avatarsDir;
    
    public function __construct(\App\Models\UsersManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function injectAvatarsDir(\App\Controls\Avatars $avatars)
    {
        $this->avatarsDir = $avatars;
    }

    public function renderDefault($page = 1)
    {
        $avatars   = $this->getManager()->getAllFluent()->where('user_avatar IS NOT NULL');       
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
    
    public function handleDeleteAvatar($user_id, $avatar_name)
    {        
        \Nette\Utils\FileSystem::delete($this->avatarsDir->getDir() . DIRECTORY_SEPARATOR . $avatar_name);
        
        $this->getManager()->update($user_id, \Nette\Utils\ArrayHash::from(['user_avatar' => null]));
        
        $this->flashMessage('Avatar was deleted.', self::FLASH_MESSAGE_SUCCESS);
        
        $this->redirect('this');
    }

        //put your code here
    protected function createComponentEditForm() {
        return null;
    }
}
