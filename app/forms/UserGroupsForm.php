<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\GroupEntity;
use App\Models\User2GroupManager;
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
     * @var User2GroupManager
     */
    private User2GroupManager $users2GroupsManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    /**
     * UserGroupsForm constructor.
     *
     * @param User2GroupManager $users2GroupsManager
     * @param ITranslator         $translator
     */
    public function __construct(
        User2GroupManager $users2GroupsManager,
        ITranslator         $translator,
        private readonly EntityManagerDecorator $em
    ) {
        parent::__construct();
        
        $this->users2GroupsManager = $users2GroupsManager;
        $this->translator          = $translator;
    }

    /**
     * UserGroupsForm render
     */
    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'userGroupsForm.latte');
        $this->template->setTranslator($this->translator);

        $groups = $this->em
            ->getRepository(GroupEntity::class)
            ->findAll();
        
        $this->getTemplate()->groups   = $groups;
        $this->template->myGroups = array_values(
            $this->users2GroupsManager->getPairsByLeft(
                $this->getPresenter()->getParameter('id')
            )
        );
        
        $this->getTemplate()->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGroupFrom(): BootstrapForm
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
    public function groupSuccess(Form $form, ArrayHash $values): void
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getPresenter()->getParameter('id');
        
        $this->users2GroupsManager->addByLeft((int) $user_id, array_values($groups));
        $this->getPresenter()->flashMessage('Group was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->getPresenter()->redirect('User:edit', $user_id);
    }
}
