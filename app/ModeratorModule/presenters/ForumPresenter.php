<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Model\Repository\UserRepository;
use Nette\Application\UI\Presenter;

class ForumPresenter extends Presenter
{
    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {
        parent::__construct();
    }

    public function actionDefault(int $page = 1): void
    {
    }

    public function renderDefault(int $page = 1): void
    {
        $userEntity = $this->userRepository
            ->findOneByNetteUser($this->getUser());

        $this->getTemplate()->forums = $userEntity->moderatorUsers;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->addTextArea('rules', 'Forum rules');

        $form->addSubmit('send', 'Save');

        $form->onSuccess[]  = [$this, 'editFormSuccess'];
        $form->onValidate[] = [$this, 'editFormValidate'];

        return $form;
    }
    
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        //$this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
    }
}
