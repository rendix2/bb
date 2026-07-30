<?php

namespace App\ForumModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Controls\UserSearchControl;
use App\Forms\ReportForm;
use App\Models\PmManager;
use App\Models\ReportManager;
use App\Models\UsersManager;
use App\Presenters\Base\AuthenticatedPresenter;
use App\Presenters\crud\CrudPresenter;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of PmPresenter
 *
 * @author rendix2
 * @method PmManager getManager()
 * @package App\ForumModule\Presenters
 */
class PmPresenter extends AuthenticatedPresenter
{
    /**
     * @var ReportManager $reportsManager
     * @inject
     */
    public ReportManager $reportsManager;

    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public UsersManager $usersManager;

    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    public function __construct(
        PmManager $manager,
        private readonly UserSearchControl $userSearchControl,
        private readonly ReportForm $reportForm,
    ) {
        parent::__construct($manager);
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);

        parent::checkRequirements($element);
    }

    public function startup()
    {
        parent::startup();

        $this->translator = $this->translatorFactory->getForumTranslator();

        $this->getTemplate()->setTranslator($this->translator);

        $this->getTemplate()->pm_count = $this->getManager()->getCountSent();
    }

    public function handleSetUserId($user_id, $user_name): void
    {
        $this->redirect('Pm:edit', ['user_id' => $user_id, 'user_name' => $user_name]);
    }

    /**
     * @param int $id
     */
    public function actionDelete(int $id)
    {
        if (!is_numeric($id)) {
            $this->error('Parameter is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     * @param int $page
     */
    public function actionDefault(int $page = 1): void
    {
        $items = $this->getManager()->getAllFluent();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, 20, 5, $page);
        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage(sprintf('No %s.', $this->getTitle()), self::FLASH_MESSAGE_DANGER);
        }

        $this->template->items      = $items->fetchAll();
        $this->template->countItems = $paginator->getCount();
    }

    /**
     * @param int $page
     */
    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    /**
     *
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        if (!$id) {
            $this['editForm']->setDefaults(
                [
                    'pm_user_id_to' => $this->getParameter('user_id'),
                    'user_name' => $this->getParameter('user_name'),
                ],
            );
        }

        parent::renderEdit($id);

        if ($id && $this->getTemplate()->item->pm_status === 'sent') {
            $this->getManager()->update($id, ArrayHash::from(['pm_status' => 'read', 'pm_time_read' => time()]));
        }
    }

    /**
     * @param int $pm_id
     */
    public function renderReport($pm_id): void
    {
    }

    /**
     *
     * @return ReportForm
     */
    protected function createComponentReportForm(): ReportForm
    {
        return $this->reportForm;
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
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
    protected function createComponentUserSearch(): UserSearchControl
    {
        return $this->userSearchControl;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_pms'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default', 'text' => 'menu_pms'],
            2 => ['text' => 'menu_pm'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbUserSearch(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default', 'text' => 'menu_pms'],
            2 => ['text' => 'pm_add_new'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbReport(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Pm:default', 'text' => 'menu_pms'],
            2 => ['text' => 'pm_report'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

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

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function editFormValidate(Form $form, ArrayHash $values): void
    {
        if (!$values->pm_user_id_to) {
            $form->addError('We are missing recipients user ID', true);
        }

        if ((int) $values->pm_user_id_to === $this->getUser()->getId()) {
            $form->addError('You cannot send PM to yourself.', true);
        }
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $values->pm_user_id_from = $this->getUser()->getId();
        $values->pm_time_sent = time();
        unset($values->user_name);

        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->getManager()->update($id, $values);
            } else {
                $result = $id = $this->getManager()->add($values);
            }

            if ($result) {
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Nothing to save.', self::FLASH_MESSAGE_INFO);
            }
        } catch (DriverException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }
}
