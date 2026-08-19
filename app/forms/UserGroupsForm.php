<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Repository\GroupRepository;
use App\Model\Repository\UserRepository;
use App\Models\User2GroupManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of UserGroupsForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserGroupsForm extends Control
{

    private User2GroupManager $users2GroupsManager;
    
    private Translator $translator;

    public function __construct(
        User2GroupManager $users2GroupsManager,
        Translator         $translator,
        private readonly EntityManagerDecorator $em,

        private readonly UserRepository $userRepository,
        private readonly GroupRepository $groupRepository,
        private readonly User $user,
    ) {
        parent::__construct();
        
        $this->users2GroupsManager = $users2GroupsManager;
        $this->translator          = $translator;
    }

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

    protected function createComponentGroupFrom(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [$this, 'groupSuccess'];

        return $form;
    }
    
    public function groupSuccess(Form $form, ArrayHash $values): void
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getPresenter()->getParameter('id');

        $userEntity = $this->userRepository
            ->findOneByNetteUser($this->user);

        foreach ($groups as $group) {
            $groupEntity = $this->groupRepository
                ->findOneBy(
                    [
                        'id' => $group
                    ]
                );

            $userGroupPermissionEntity = new UserGroupMemberEntity();
            $userGroupPermissionEntity->user = $userEntity;
            $userGroupPermissionEntity->group = $groupEntity;

            $this->em->persist($userGroupPermissionEntity);
        }

        $this->em->flush();

        $this->getPresenter()->flashMessage('Group was saved.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->getPresenter()->redirect('User:edit', $user_id);
    }
}
