<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchPostForm
 *
 * @author rendi
 */
class SearchPostForm extends Control
{
    /**
     *
     * @var \Nette\Localization\ITranslator $translator 
     */
    private $translator;
    
    /**
     * 
     * @param \Nette\Localization\ITranslator $translator
     */
    public function __construct(\Nette\Localization\ITranslator $translator)
    {                
        parent::__construct();
        
        $this->translator = $translator;
    }
    
    /**
     * 
     */
    public function render()
    {
        $this['searchPostForm']->render();
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchPostForm()
    {
        $form = BootstrapForm::create();

        $form->setTranslator($this->translator);
        $form->addText('search_post', 'Post')->setRequired('Please enter some in post');
        $form->addSubmit('send_post', 'Search post');
        $form->onSuccess[] = [$this, 'searchPostFormSuccess'];

        return $form;
    }    
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchPostFormSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter->redirect('Search:postResults', $values->search_post);
    }
}
