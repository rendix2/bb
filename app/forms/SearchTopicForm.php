<?php

namespace App\Forms;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchTopicForm
 *
 * @author rendix2
 * @package App\Forms
 */
class SearchTopicForm extends Control
{

    public function __construct(private ITranslator $translator)
    {
        parent::__construct();

    }

    public function render(): void
    {
        $this['searchTopicForm']->render();
    }
    
    public function createComponentSearchTopicForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addText('search_topic', 'Topic')->setRequired('Please enter some in topic');
        $form->addSubmit('send_topic', 'Search topic');
        $form->onSuccess[] = [$this, 'searchTopicFormSuccess'];

        return $form;
    }
    
    public function searchTopicFormSuccess(Form $form, ArrayHash $values)
    {
        $this->presenter->redirect('Search:topicResults', $values->search_topic);
    }
}
