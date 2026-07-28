<?php

namespace App\Controls;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of UserSearchControl
 *
 * @author rendix2
 * @package App\Controls
 */
class UserSearchControl extends Control
{
    /**
     *
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;
    
    /**
     *
     * @param UsersManager $usersManager
     * @param ITranslator  $translator
     *
     */
    public function __construct(
        UsersManager $usersManager,
        ITranslator  $translator
    ) {
        parent::__construct();
        
        $this->usersManager = $usersManager;
        $this->translator   = $translator;
    }

    /**
     * renders controls
     */
    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->getTemplate()->setFile(__DIR__ . $sep . 'templates' . $sep . 'userSearch' . $sep . 'userSearch.latte');

        if (!isset($template->users)) {
            $template->users = [];
        }
        
        $template->render();
    }

    protected function createComponentUserSearch(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setAjax(true);
        $form->setTranslator($this->translator);
        
        $form->addHidden('id');
        $form->addText('username', 'User name:')
            ->setRequired(true);

        $form->addSubmit('send');
        $form->onSuccess[] = [$this, 'success'];
       
        return $form;
    }
    
    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function success(Form $form, ArrayHash $values): void
    {
        $users = $this->usersManager->findLikeByUserName($values->username);
        
        if (!count($users)) {
            $this->getPresenter()->flashMessage('User was not found.', BasePresenter::FLASH_MESSAGE_DANGER);
        }
        
        $this->getTemplate()->users = $users;

        $this->redrawControl('users');
    }
}
