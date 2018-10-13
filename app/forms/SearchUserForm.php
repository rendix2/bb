<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchUserForm
 *
 * @author rendi
 */
class SearchUserForm extends Control
{
    
    /**
     * 
     */
    public function render()
    {
        $this['searchUserForm']->render();
    }
    
    /**
     * @return BootstrapForm
     */
    public function createComponentSearchUserForm()
    {
        $form = BootstrapForm::create();

        $form->addText('search_user', 'User')->setRequired('Please enter name.');
        $form->addSubmit('send_user', 'Search user');
        $form->onSuccess[] = [$this, 'searchUserFormSuccess'];

        return $form;
    }


    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchUserFormSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter->redirect('Search:userResults', $values->search_user);
    }    
    
}
