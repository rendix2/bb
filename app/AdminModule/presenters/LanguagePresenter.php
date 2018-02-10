<?php

namespace App\AdminModule\Presenters;

/**
 * Description of LanguagePresenter
 *
 * @author rendi
 */
class LanguagePresenter extends Base\AdminPresenter {

    private $userManager;

    public function __construct(\App\Models\LanguagesManager $manager) {
        parent::__construct($manager);
    }

    public function injectUserManager(\App\Models\UsersManager $userManager) {
        $this->userManager = $userManager;
    }

    public function renderEdit($id = null) {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->userManager->getCountByLangId($id);
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('lang_name', 'Language name:')->setRequired();

        return $this->addSubmitB($form);
    }

}
