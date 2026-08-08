<?php

namespace App\AdminModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Models\ReportManager;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of ReportPresenter
 *
 * @author rendix2
 * @method ReportManager getManager()
 * @package App\AdminModule\Presenters
 */
class ReportPresenter extends Presenter
{
    public function __construct(
        private readonly Translator $translator
    )
    {
        parent::__construct();
    }

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

    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    public function renderEdit(int $id): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this['editForm']->setDefaults([]);
        }
    }
    
    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $values = [
            0 => 'Added',
            1 => 'Fixed'
        ];
        
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addSelect('report_status', 'Report status:', $values);

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

        return $form;
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
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
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->translator);

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('report_id', 'report_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('report_time', 'report_time', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('user_name', 'reporter_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('forum_name', 'report_forum', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('topic_name', 'report_topic', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('post_title', 'report_post', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('reported_user_name', 'reported_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(
            'report_status',
            'report_status',
            GridFilter::CHECKBOX_LIST,
            [
                1 => 'report_solved',
                0 => 'report_added'
            ]
        );
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_reports']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',  'text' => 'menu_index'],
            1 => ['link' => 'Report:default', 'text' => 'menu_reports'],
            2 => ['link' => 'Report:edit',    'text' => 'menu_report'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
