<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchUserForm
 *
 * @author rendix2
 * @package App\Forms
 */
class SearchUserForm extends Control
{
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;
    
    /**
     *
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator)
    {
        parent::__construct();
        
        $this->translator = $translator;
    }
    
    /**
     *
     */
    public function __destruct()
    {
        $this->translator = null;
    }
    
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

        $form->setTranslator($this->translator);
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
