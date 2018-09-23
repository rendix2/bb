<?php

namespace App\ForumModule\Presenters;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Services\UserLoginFormFactory;
use App\Forms\UserLoginForm;
use Nette\Utils\ArrayHash;

/**
 * Description of LoginPresenter
 *
 * @author rendix2
 */
class LoginPresenter extends BasePresenter
{
    /**
     * @var string $backlink
     * @persistent
     */
    public $backlink = '';
    
    /**
     *
     * @var UserLoginFormFactory $userLoginForm
     * @inject
     */
    public $userLoginForm;

    /**
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;
    
    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }
    
    /**
     * before render method
     * sets translator
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translatorFactory->forumTranslatorFactory());
    }

    /**
     * @param int    $user_id
     * @param string $key
     */
    public function actionActivate($user_id, $key)
    {
        $ok = true;

        if (!$user_id || !$key) {
            $this->flashMessage('Parameters are not set.', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->flashMessage('You are logged in!', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        // after register
        $can = $this->usersManager->canBeActivated($user_id, $key);

        if (!$can) {
            $this->flashMessage('You cannot be activated.', self::FLASH_MESSAGE_DANGER);
            $ok = false;
        }

        if ($ok) {
            $this->usersManager
                ->update($user_id, ArrayHash::from([
                    'user_active' => 1,
                    'user_activation_key' => null
                ]));
            $this->flashMessage('You have been activated.', self::FLASH_MESSAGE_SUCCESS);
            $this->redirect('Login:default');
        } else {
            $this->redirect('Index:default');
        }
    }
    
    /**
     *
     * @return UserLoginForm
     */
    public function createComponentLoginForm()
    {
        return $this->userLoginForm->create();
    }
}
