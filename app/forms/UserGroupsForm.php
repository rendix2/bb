<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\GroupsManager;
use App\Models\Users2GroupsManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of UserGroupsForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserGroupsForm extends Control
{

    /**
     *
     * @var GroupsManager $groupsManager
     */
    private $groupsManager;

    /**
     *
     * @var Users2GroupsManager
     */
    private $users2GroupsManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;

    /**
     *
     * @param GroupsManager       $groupsManager
     * @param Users2GroupsManager $users2GroupsManager
     * @param ITranslator         $translator
     */
    public function __construct(
        GroupsManager       $groupsManager,
        Users2GroupsManager $users2GroupsManager,
        ITranslator         $translator
    ) {
        parent::__construct();
        
        $this->groupsManager       = $groupsManager;
        $this->users2GroupsManager = $users2GroupsManager;
        $this->translator          = $translator;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->groupsManager       = null;
        $this->users2GroupsManager = null;
        $this->translator          = null;
    }

    /**
     *
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep. 'templates' . $sep . 'userGroupsForm.latte');
        $this->template->setTranslator($this->translator);
        
        $this->template->groups   = $this->groupsManager->getAllCached();
        $this->template->myGroups = array_values($this->users2GroupsManager->getPairsByLeft($this->getPresenter()->getParameter('id')));
        
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGroupFrom()
    {
        $form = BootstrapForm::create();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [$this, 'groupSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function groupSuccess(Form $form, ArrayHash $values)
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->presenter->getParameter('id');
        
        $this->users2GroupsManager->addByLeft((int) $user_id, array_values($groups));
        $this->presenter->flashMessage('Group was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->presenter->redirect('User:edit', $user_id);
    }
}
