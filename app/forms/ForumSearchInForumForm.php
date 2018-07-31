<?php

namespace App\Forms;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use App\Controls\BootstrapForm;

/**
 * Description of ForumSearchInForumForm
 *
 * @author rendi
 */
class ForumSearchInForumForm extends Control
{
    
    /**
     *
     * @var ITranslator $translator 
     */
    private $translator;
    
    public function __construct(ITranslator $translator)
    {
        parent::__construct();
        
        $this->translator = $translator;
    }
    
    public function render()
    {       
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'searchInForumForm.latte');
        $template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentSearchInForumForm()
    {
         $form = BootstrapForm::create();
         $form->setTranslator($this->translator);
         
         $form->addText('search_form', 'Search forum:');
         $form->addSubmit('submit', 'Search');
         $form->onSuccess[] = [$this, 'searchInForumFormSuccess'];
         
         return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchInForumFormSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter->redirect(
            'Forum:default',
            $this->presenter->getParameter('forum_id'),
            $this->presenter->getParameter('page'),
            $values->search_form
        );
    }    
    
}
