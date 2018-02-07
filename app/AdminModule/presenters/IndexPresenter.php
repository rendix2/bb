<?php

namespace App\AdminModule\Presenters;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends \App\Presenters\Base\BasePresenter {

    //put your code here

    public function beforeRender() {
        parent::beforeRender();

        $this->template->setTranslator(new \App\Translator('Admin', $this->getUser()->getIdentity()->getData()['lang_file_name']));
    }

    public function renderDefault() {
        
    }

}
