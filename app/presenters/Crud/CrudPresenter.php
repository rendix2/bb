<?php

namespace App\Presenters\crud;

use App\Controls\BootStrapForm;
use App\Models\Crud\CrudManager;
use App\Presenters\Base\ManagerPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of CrudPresenter
 *
 * @author rendi
 * @method CrudManager getManager()
 */
abstract class CrudPresenter extends ManagerPresenter
{

    const CACHE_KEY_PRIMARY_KEY = 'BBPrimaryKeys';

    private $title;

    abstract protected function createComponentEditForm();

    public function __construct(CrudManager $manager)
    {
        parent::__construct($manager);
    }

    public function getTitle()
    {
        $name  = explode(':', $this->getName());
        $count = count($name);        
        $name  = $name[$count-1];
                
        return $this->title ? $this->title : $name;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitleOnAdd()
    {
        return $this->title . ' add';
    }

    public function getTitleOnDefault()
    {
        return $this->title . ' list';
    }

    public function getTitleOnEdit()
    {
        return $this->title . ' edit';
    }

    protected function addSubmit(Form $form)
    {
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[] = [
            $this,
            'editFormSuccess'
        ];

        return $form;
    }

    protected function addSubmitB(BootStrapForm $form)
    {
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[] = [
            $this,
            'editFormSuccess'
        ];

        return $form;
    }

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

    public function onValidate(Form $form, ArrayHash $values)
    {

    }

    public function actionDelete($id)
    {
        if (!is_numeric($id)) {
            $this->error('Param id is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    public function renderDefault()
    {
        $items = $this->getManager()->getAllFluent();
        $count = count($items);

        if (!$count) {
            $this->flashMessage('No items', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->items      = $items->fetchAll();
        $this->template->title      = $this->getTitleOnDefault();
        $this->template->countItems = $count; 
    }

    public function renderEdit($id = null)
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->item = [];

            $this['editForm']->setDefaults([]);
        }
    }

}
