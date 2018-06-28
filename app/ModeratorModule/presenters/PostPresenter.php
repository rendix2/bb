<?php

namespace App\ModeratorModule;

use App\Controls\BootstrapForm;
use App\Models\PostsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendi
 */
class PostPresenter extends ModeratorPresenter
{
    /**
     * PostPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     *
     */
    protected function createComponentEditForm()
    {
        $form = new BootstrapForm();
        
        $form->addTextArea('post_text', 'Post:');
        $form->addSubmit('send', 'Save');
        
        return $this->addSubmitB($form);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
    }
}
