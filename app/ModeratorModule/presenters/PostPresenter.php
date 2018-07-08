<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PostsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendi
 */
class PostPresenter extends \App\ModeratorModule\Presenters\Base\ModeratorPresenter
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
        
        $form->addText('post_title', 'Post title:');
        $form->addTextArea('post_text', 'Post:');
        $form->addCheckbox('post_locked', 'Post locked:');
        $form->addSubmit('send', 'Save');
        
        return $this->addSubmitB($form);
    }
}
