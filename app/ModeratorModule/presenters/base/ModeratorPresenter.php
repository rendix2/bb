<?php

/**
 * Description of ModeratorPresenter
 *
 * @author rendi
 */
abstract class ModeratorPresenter extends App\Presenters\crud\CrudPresenter {
    //put your code here
    
    public function startup(){
        parent::startup();
        
        if (!$this->getUser()->isInRole('moderator')){
            $this->error('You are not in moderator role!s');
        }
    }
    
    public function beforeRender() {
        parent::beforeRender();
        
        $this->template->setTranslator();
    }
}
