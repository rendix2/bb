<?php

namespace App\AdminModule\Presenters;

use App\Presenters\Base\BasePresenter;
use App\Translator;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends BasePresenter
{
    private $appDir;
    
    public function injectWwwDir(\App\Controls\AppDir $appDir){
        $this->appDir = $appDir;
    }
    
    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();
        $lang_name = $this->getUser()->getIdentity()->getData()['lang_file_name'];

        $this->template->setTranslator(new Translator($this->appDir,'admin', $lang_name));
    }

    /**
     *
     */
    public function renderDefault()
    {

    }

}
