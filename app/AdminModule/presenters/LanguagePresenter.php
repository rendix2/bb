<?php

namespace App\AdminModule\Presenters;

use App\Controls\GridFilter;
use App\Models\LanguagesManager;
use App\Models\UsersManager;
use App\Controls\BreadCrumbControl;

/**
 * Description of LanguagePresenter
 *
 * @author rendix2
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

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->userManager->getCountByLang($id);
    }

    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getAdminTranslator());
            
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
        
        return new BreadCrumbControl($breadCrumb, $this->getAdminTranslator());
    }
    
    /**
     * @return BreadCrumbControl
     */    
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Language:default', 'text' => 'menu_languages'],            
            2 => ['link' => 'Language:edit',     'text' => 'menu_language'],
        ];       
        
        return new BreadCrumbControl($breadCrumb, $this->getAdminTranslator());        
    }      
}
