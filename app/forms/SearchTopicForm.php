<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
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
        $this['searchTopicForm']->render();
    }
    
    /**
     * @return BootstrapForm
     */
    public function createComponentSearchTopicForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translator);

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
