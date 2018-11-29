<?php

namespace App\Presenters\crud;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Models\Crud\CrudManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of CrudPresenter
 *
 * @author rendix2
 * @package App\Presenters\crud
 */
abstract class CrudPresenter extends AuthenticatedPresenter
{
    /**
     * @var string
     */
    const FORM_NAME = 'editForm';

    /**
     * @var string
     */
    const FORM_ON_SUCCESS = 'editFormSuccess';

    /**
     * @var string
     */
    const FORM_ON_VALIDATE = 'onValidate';
    
    /**
     * @var int
     */
    const ITEMS_PER_PAGE = 20;
    
    /**
     * @var GridFilter $gf
     */
    protected $gf;
    
    /**
     * @var string $title
     */
    private $title;
    
    /**
     *
     * @var CrudManager $manager
     */
    private $manager;

    /**
     * @return Form
     */
    abstract protected function createComponentEditForm();
    
    /**
     * @return GridFilter
     */
    abstract protected function createComponentGridFilter();

    /**
     * CrudPresenter constructor.
     *
     * @param CrudManager $manager
     */
    public function __construct(CrudManager $manager)
    {
        parent::__construct();
        
        $this->manager = $manager;
    }
    
    /**
     * CrudPresenter destructor.
     */
    public function __destruct()
    {
        $this->gf      = null;
        $this->title   = null;
        $this->manager = null;
        
        parent::__destruct();
    }

    /**
     *
     * @return CrudManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        $name  = explode(':', $this->getName());
        $count = count($name);
        $name  = $name[$count - 1];

        return $this->title ?: $name;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string string
     */
    public function getTitleOnAdd()
    {
        return $this->title . ' add';
    }

    /**
     * @return string
     */
    public function getTitleOnDefault()
    {
        return $this->title . ' list';
    }

    /**
     * @return string
     */
    public function getTitleOnEdit()
    {
        return $this->title . ' edit';
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    protected function addSubmit(Form $form)
    {
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[]  = [$this, self::FORM_ON_SUCCESS];
        $form->onValidate[] = [$this, self::FORM_ON_VALIDATE];

        return $form;
    }

    /**
     * @param BootstrapForm $form
     *
     * @return BootstrapForm
     */
    protected function addSubmitB(BootstrapForm $form)
    {
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[]  = [$this, self::FORM_ON_SUCCESS];
        $form->onValidate[] = [$this, self::FORM_ON_VALIDATE];

        return $form;
    }

    /**
     * @param GridFilter $gridFilter
     */
    public function injectGridFilter(GridFilter $gridFilter)
    {
        $this->gf = $gridFilter;
    }

    /**
     * @param Form      $form   form
     * @param ArrayHash $values values
     */
    public function onValidate(Form $form, ArrayHash $values)
    {
    }

    /**
     * @param int $id
     */
    public function actionDelete($id)
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
    public function actionDefault($page = 1)
    {
        if (isset($this['gridFilter'])) {
            $this->getComponent('gridFilter');
        }

        $items = $this->getManager()->getAllFluent();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, static::ITEMS_PER_PAGE, 5, $page);
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
    public function renderDefault($page = 1)
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this[self::FORM_NAME]->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this[self::FORM_NAME]->setDefaults([]);
        }
    }
    
    /**
     * @param Form      $form   form
     * @param ArrayHash $values values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
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
