<?php

namespace App\Forms;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchUserForm
 *
 * @author rendix2
 * @package App\Forms
 */
class SearchUserForm extends Control
{
    public function __construct(
        private readonly Translator $translator,
    )
    {
    }

    public function render(): void
    {
        $this['searchUserForm']->render();
    }
    
    public function createComponentSearchUserForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->setTranslator($this->translator);
        $form->addText('search_user', 'User')->setRequired('Please enter name.');
        $form->addSubmit('send_user', 'Search user');
        $form->onSuccess[] = [$this, 'searchUserFormSuccess'];

        return $form;
    }

    public function searchUserFormSuccess(Form $form, ArrayHash $values): void
    {
        $this->getPresenter()->redirect('Search:userResults', $values->search_user);
    }
}
