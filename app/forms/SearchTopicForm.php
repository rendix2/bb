<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchTopicForm
 *
 * @author rendi
 */
class SearchTopicForm extends Control
{
    
    /**
     * 
     */
    public function render()
    {
        $this['searchTopicForm']->render();
    }
    
    /**
     * @return BootstrapForm
     */
    public function createComponentSearchTopicForm()
    {
        $form = BootstrapForm::create();

        $form->addText('search_topic', 'Topic')->setRequired('Please enter some in topic');
        $form->addSubmit('send_topic', 'Search topic');
        $form->onSuccess[] = [$this, 'searchTopicFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchTopicFormSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter->redirect('Search:topicResults', $values->search_topic);
    }    
}
