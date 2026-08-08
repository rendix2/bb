<?php

namespace App\Forms;


use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchPostForm
 *
 * @author rendix2
 * @package App\Forms
 */
class SearchPostForm extends Control
{
    
    public function __construct(
        private readonly Translator $translator
    )
    {
    }

    public function render(): void
    {
        $this['searchPostForm']->render();
    }

    public function createComponentSearchPostForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->setTranslator($this->translator);
        $form->addText('search_post', 'Post')->setRequired('Please enter some in post');
        $form->addSubmit('send_post', 'Search post');
        $form->onSuccess[] = [$this, 'searchPostFormSuccess'];

        return $form;
    }
    
    public function searchPostFormSuccess(Form $form, ArrayHash $values): void
    {
        $this->getPresenter()->redirect('Search:postResults', $values->search_post);
    }
}
