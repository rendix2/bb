<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Models\LanguagesManager;
use App\Models\UsersManager;

/**
 * Description of LanguagePresenter
 *
 * @author rendix2
 * @method LanguagesManager getManager()
 * @package App\AdminModule\Presenters
 */
class LanguagePresenter extends AdminPresenter
{
    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;

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
     * LanguagePresenter destructor.
     */
    public function __destruct()
    {
        $this->usersManager = null;
        
        parent::__destruct();
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->usersManager->getCountByLang($id);
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('lang_id', 'lang_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('lang_name', 'lang_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
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

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_languages']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',    'text' => 'menu_index'],
            1 => ['link' => 'Language:default', 'text' => 'menu_languages'],
            2 => ['link' => 'Language:edit',    'text' => 'menu_language'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
