<?php

namespace App\AdminModule\Presenters;

use App\Controls\GridFilter;
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
     * @inject
     */
    public $userManager;

    /**
     * LanguagePresenter constructor.
     *
     * @param LanguagesManager $manager
     */
    public function __construct(LanguagesManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function startup()
    {
        parent::startup();
        
        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('lang_id', 'lang_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('lang_name', 'lang_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->userManager->getCountByLang($id);
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('lang_name', 'Language name:')->setRequired();

        return $this->addSubmitB($form);
    }
}
