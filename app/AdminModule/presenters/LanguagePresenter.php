<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\LanguagesManager;
use App\Models\UsersManager;

/**
 * Description of LanguagePresenter
 *
 * @author rendi
 */
class LanguagePresenter extends Base\AdminPresenter
{
    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * LanguagePresenter constructor.
     *
     * @param LanguagesManager $manager
     */
    public function __construct(LanguagesManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->userManager->getCountByLangId($id);
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText(
            'lang_name',
            'Language name:'
        )
            ->setRequired();

        return $this->addSubmitB($form);
    }
}
