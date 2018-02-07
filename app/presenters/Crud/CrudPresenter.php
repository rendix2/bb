<?php

namespace App\Presenters\crud;

/**
 * Description of CrudPresenter
 *
 * @author rendi
 * @method \App\Models\Crud\CrudManager getManager()
 */
abstract class CrudPresenter extends \App\Presenters\Base\ManagerPresenter {

    const CACHE_KEY_PRIMARY_KEY = 'BBPrimaryKeys';
    
    private $title;

    public function __construct(\App\Models\Crud\CrudManager $manager) {
        parent::__construct($manager);         
    }       

    public function getTitleOnEdit(){
        return $this->title . ' edit';
    }

    public function getTitleOnAdd(){
        return $this->title . ' add';
    }
    
    public function getTitleOnDefault(){
        return $this->title . ' list';
    }

    public function getTitle(){
        return $this->title ? $this->title : $this->getName();;
    }

    public function setTitle($title){
        $this->title = $title;
        
       return $this;
    }

    public function renderDefault() {
        $items = $this->getManager()->getAllFluent();

        if (!count($items)) {
            $this->flashMessage('No items');
        }

        $this->template->items = $items->fetchAll();
        $this->template->title = $this->getTitleOnDefault();
    }

    public function renderEdit($id = null) {
        if ($id) {           
            if ( !is_numeric($id) ){
                $this->error('Param id is not numeric.');
            }
            
            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #'.$id.' not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item  = $item;
            $this->template->title = $this->getTitleOnEdit(); 
        }
        else{
            $this->template->title = $this->getTitleOnAdd();
            
             $this['editForm']->setDefaults([]);
        }
    }

    public function actionDelete($id) {
        if (!is_numeric($id)) {
            $this->error('Param id is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.');
        } else {
            $this->flashMessage('Item was not deleted.');
        }

        $this->redirect(':'.$this->getName().':default');
    }

    abstract protected function createComponentEditForm();
    
    protected function addSubmit(\Nette\Application\UI\Form $form){
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[] = [$this, 'editFormSuccess'];
        
        return $form;
    }
    
    protected function addSubmitB(\App\Controls\BootStrapForm $form){
        $form->addSubmit('Send', 'Send');
        $form->onSuccess[] = [$this, 'editFormSuccess'];
        
        return $form;
    }

    public function editFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $id = $this->getParameter('id');
        
        if ( $id ){
            $result = $this->getManager()->update($id, $values);
        }
        else{
             $result = $id = $this->getManager()->add($values);
        }
        
        if ($result){
            $this->flashMessage($this->getTitle() .' was saved.');                     
        }
        else{
            $this->flashMessage('Nothing to save.');
        }
        
        $this->redirect(':'.$this->getName().':default');
    }

    public function onValidate(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        
    }

}
