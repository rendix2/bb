<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Models\FaqManager;
use Contributte\Datagrid\Datagrid;
use Contributte\FormsBootstrap\BootstrapForm;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 * @method FaqManager getManager()
 * @package App\AdminModule\Presenters
 */
class FaqPresenter extends AdminPresenter
{

    /**
     * @var ITranslator $adminTranslator
     */
    private ITranslator $adminTranslator;

    public function __construct(FaqManager $manager)
    {
        parent::__construct($manager);
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();

        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);

        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->adminTranslator);
    }

    public function startup()
    {
        parent::startup();

        $this->adminTranslator = $this->translatorFactory->getAdminTranslator();
    }

    public function getTranslator(): ITranslator
    {
        return $this->adminTranslator;
    }




    protected function createComponentEditForm(): BootstrapForm
    {
        $form = new BootstrapForm();

        return $form;
    }


    protected function createComponentDataGrid(): Datagrid
    {
        $dataGrid = new Datagrid();

        return $dataGrid;
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
     * @param int|null $id
     */
    public function renderEdit(int $id = null): void
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

    /**
     * @param Form      $form   form
     * @param ArrayHash $values values
     */
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

}
