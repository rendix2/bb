<?php

namespace App\ModeratorModule;

use App\Models\PostsManager;

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
        $form = new \App\Controls\BootstrapForm();
        
        $form->addTextArea('post_text', 'Post:');
        $form->addSubmit('send', 'Save');
        
        return $this->addSubmitB($form);
    }
    
    public function editFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        
    }
}
