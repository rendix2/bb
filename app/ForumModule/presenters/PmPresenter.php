<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\UserSearchControl;
use App\Forms\ReportForm;
use App\Models\PmManager;
use App\Models\ReportsManager;
use App\Models\UsersManager;
use App\Presenters\crud\CrudPresenter;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of PmPresenter
 *
 * @author rendix2
 * @method PmManager getManager()
 * @package App\ForumModule\Presenters
 */
class PmPresenter extends CrudPresenter
{
    
    /**
     * @var ReportsManager $reportsManager
     * @inject
     */
    public $reportsManager;
    
    /**
     *
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
     * PmPresenter destructor.
     */
    public function __destruct()
    {
        $this->reportsManager = null;
        $this->translator     = null;
        
        parent::__destruct();
    }

    /**
     *
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $this->user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }
    
    /**
     * PmPresenter startup.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();

        $this->translator = $this->translatorFactory->createForumTranslatorFactory();

        $this->template->setTranslator($this->translator);

        $this->template->pm_count = $this->getManager()->getCountSent();
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
            $this[self::FORM_NAME]->setDefaults([
                'pm_user_id_to' => $this->getParameter('user_id'),
                'user_name'     => $this->getParameter('user_name')
            ]);
        }
        
        parent::renderEdit($id);

        if ($id && $this->template->item->pm_status === 'sent') {
            $this->getManager()->update($id, ArrayHash::from(['pm_status' => 'read', 'pm_time_read' => time()]));
        }
    }

    /**
     * @param int $pm_id
     */
    public function renderReport($pm_id)
    {
    }

    /**
     *
     * @return ReportForm
     */
    protected function createComponentReportForm()
    {
        return new ReportForm($this->reportsManager);
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
        $this->gf->addFilter('pm_status', 'pm_status', GridFilter::CHECKBOX_LIST, ['sent' => 'Sent', 'read' => 'Read']);
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
            1 => ['text' => 'menu_pms']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default',    'text' => 'menu_pms'],
            2 => ['text' => 'menu_pm']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbUserSearch()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default',    'text' => 'menu_pms'],
            2 => ['text' => 'pm_add_new']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default',    'text' => 'menu_pms'],
            2 => ['text' => 'pm_report']
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
        $form->addText('user_name', 'User name:')
            ->setDisabled();

        if (!$this->getParameter('id')) {
            $form->addText('pm_subject', 'PM Subject:')
                ->setRequired(true);
            $form->addTextArea('pm_text', 'PM Text:')
                ->setRequired(true);
        }

        return $this->addSubmitB($form);
    }
    
    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function onValidate(Form $form, ArrayHash $values)
    {
        if (!$values->pm_user_id_to) {
            $form->addError('We are missing recipients user ID', true);
        }
        
        if ((int)$values->pm_user_id_to === $this->user->id) {
            $form->addError('You cannot send PM to yourself.', true);
        }
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $values->pm_user_id_from = $this->user->id;
        $values->pm_time_sent    = time();
        unset($values->user_name);
        
        parent::editFormSuccess($form, $values);
    }
}
