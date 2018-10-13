<?php

namespace App\Controls;

use App\Models\UsersManager;
use Nette\Localization\ITranslator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use App\Presenters\Base\BasePresenter;

/**
 * Description of UserSearchControl
 *
 * @author rendix2
 */
class UserSearchControl extends Control
{
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;
    
    /**
     *
     * @param UsersManager $usersManager
     * @param ITranslator  $translator
     *
     */
    public function __construct(UsersManager $usersManager, ITranslator $translator)
    {
        parent::__construct();
        
        $this->usersManager = $usersManager;
        $this->translator   = $translator;
    }
    
    /**
     * renders controls
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'userSearch' . $sep . 'userSearch.latte');
        if (!isset($template->users)) {
            $template->users = [];
        }
        
        $template->render();
    }

    /**
     *
     * @return BootstrapForm
     */
    protected function createComponentUserSearch()
    {
        $form = BootstrapForm::createAjax();
        $form->setTranslator($this->translator);
        
        $form->addHidden('user_id');
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addSubmit('send');
        $form->onSuccess[] = [$this, 'success'];
       
        return $form;
    }
    
    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function success(Form $form, ArrayHash $values)
    {
        $users = $this->usersManager->findLikeByUserName($values->user_name);
        
        if (!count($users)) {
            $this->presenter->flashMessage('User was not found.', BasePresenter::FLASH_MESSAGE_DANGER);
        }
        
        $this->template->users = $users;

        $this->redrawControl('users');
    }
}
