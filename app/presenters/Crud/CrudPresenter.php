<?php

namespace App\Presenters\crud;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Models\Crud\CrudManager;
use App\Presenters\Base\ManagerPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of CrudPresenter
 *
 * @author rendix2
 * @method CrudManager getManager()
 */
abstract class CrudPresenter extends ManagerPresenter
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
     * @var string
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
     * @return mixed
     */
    abstract protected function createComponentEditForm();

    /**
     * CrudPresenter constructor.
     *
     * @param CrudManager $manager
     */
    public function __construct(CrudManager $manager)
    {
        parent::__construct($manager);
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
        $form->onSuccess[] = [$this, self::FORM_ON_SUCCESS];
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
        $form->onSuccess[] = [$this, self::FORM_ON_SUCCESS];
        $form->onValidate[] = [$this, self::FORM_ON_VALIDATE];

        return $form;
    }

    /**
     * @param Form      $form   form
     * @param ArrayHash $values values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $id = $this->getParameter('id');

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

        $this->getManager()->deleteCache();
        $this->redirect(':' . $this->getName() . ':default');
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
    public function renderDefault($page = 1)
    {
        if (isset($this['gridFilter'])) {
            $this->getComponent('gridFilter');
        }
        
        $items = $this->getManager()->getAllFluent();
        
        foreach ($this->gf->getWhere() as $where) {
            if (isset($where['value'])) {
                $items->where('[' . $where['column'] . '] ' . $where['type'] . ' ' . $where['strint'], $where['value']);
            }
        }

        foreach ($this->gf->getOrderBy() as $column => $type) {
            $items->orderBy($column, $type);
        }
        
        $paginator = new PaginatorControl($items, static::ITEMS_PER_PAGE, 5, $page);
        $this->addComponent($paginator, 'paginator');
        
        if (!$paginator->getCount()) {
            $this->flashMessage('No '.$this->getTitle().'.', self::FLASH_MESSAGE_DANGER);
        }
       
        $this->template->items      = $items->fetchAll();
        $this->template->title      = $this->getTitleOnDefault();
        $this->template->countItems = $paginator->getCount();
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
                $this->error('Item $' . $this->getTitle() . '['.$id.'] was not found.');
            }

            $this[self::FORM_NAME]->setDefaults($item);

            $this->template->item  = $item;
            $this->template->title = $this->getTitleOnEdit();
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->item  = [];

            $this[self::FORM_NAME]->setDefaults([]);
        }
    }
}
