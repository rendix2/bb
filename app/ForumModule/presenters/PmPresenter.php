<?php

namespace App\ForumModule\Presenters;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Models\PmManager;
use App\Models\UsersManager;
use App\Presenters\crud\CrudPresenter;
use App\Controls\GridFilter;
use App\Controls\UserSearchControl;
use App\Controls\BreadCrumbControl;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Localization\ITranslator;

/**
 * Description of PmPresenter
 *
 * @author rendi
 */
class PmPresenter extends CrudPresenter
{    
    /**
     * @var Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * PmPresenter constructor.
     *
     * @param PmManager $manager
     */
    public function __construct(PmManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * 
     * @param mixed $element
     */
    public function checkRequirements($element)
    {       
        $this->getUser()->getStorage()->setNamespace('frontend');
        
        parent::checkRequirements($element);
    }       
    
    /**
     * @return void
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory(); 
        
        $this->template->setTranslator($this->translator);
    }   
    
    /**
     * 
     * @param int    $user_id
     * @param string $user_name
     */
    public function handleSetUserId($user_id, $user_name)
    {        
        $this->redirect('Pm:edit', ['user_id' => $user_id, 'user_name' => $user_name]);
    }   
    
    /**
     * 
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {        
        if (!$id) {
            $this[self::FORM_NAME]->setDefaults(['pm_user_id_to' => $this->getParameter('user_id'), 'user_name' => $this->getParameter('user_name')]);
        }
        
        parent::renderEdit($id);
    }
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->translator);
            
        $this->gf->addFilter('pm_id', 'pm_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('user_name', 'user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('pm_subject', 'pm_subject', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('pm_status', 'pm_status', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(null, null, GridFilter::NOTHING);
            
        return $this->gf;
    }

    /**
     * 
     * @return UserSearchControl
     */
    protected function createComponentUserSearch()
    {
        return new UserSearchControl($this->usersManager, $this->translator);
    }
    
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_pm']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->createBootstrapForm();
        
        $form->setTranslator($this->translator);
        
        $form->addHidden('pm_user_id_to');
        $form->addText('pm_subject', 'PM Subject:')->setRequired(true);        
        $form->addText('user_name', 'User name:')->setDisabled();                
        $form->addTextAreaHtml('pm_text', 'PM Text:');
        
        return $this->addSubmitB($form);
    }
    
    /**
     * 
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function onValidate(Form $form, ArrayHash $values) {       
        if (!$values->pm_user_id_to) {
            $form->addError('We are missing recepients user ID', true);
        }
        
        if ((int)$values->pm_user_id_to === $this->getUser()->getId()) {
            $form->addError('You cannost send PM to yourself.', true);
        }
    }

    /**
     * 
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $values->pm_user_id_from = $this->getUser()->getId();
        unset($values->user_name);            
        
        parent::editFormSuccess($form, $values);
    }
}
