<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PMManager;
use App\Presenters\crud\CrudPresenter;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends CrudPresenter
{
    /**
     * PmPresenter constructor.
     *
     * @param PMManager $manager
     */
    public function __construct(PMManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return BootstrapForm|
     */
    protected function createComponentEditForm()
    {
        $form = self::createBootstrapForm();
        
        $form->addHidden('pm_to_user_id');
        $form->addHidden('pm_from_user_id');
        $form->addText('pm_subject', 'Subject:');
        $form->addText('pm_user_name', 'User name:');
        $form->addTextArea('pm_text', 'Text:');
        
        return $form;
    }
}
