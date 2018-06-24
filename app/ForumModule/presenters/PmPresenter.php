<?php

namespace App\ForumModule\Presenters;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends \App\Presenters\crud\CrudPresenter
{
    public function __construct(\App\Models\PMManager $manager) {
        parent::__construct($manager);
    }

    protected function createComponentEditForm() {
        $form = self::createBootstrapForm();
        
        $form->addHidden('pm_to_user_id');
        $form->addHidden('pm_from_user_id');
        $form->addText('pm_subject', 'Subject:');
        $form->addText('pm_user_name', 'User name:');
        $form->addTextArea('pm_text', 'Text:');
        
        return $form;
    }

}
