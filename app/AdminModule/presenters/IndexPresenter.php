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
    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();
        $lang_name = $this->getUser()->getIdentity()->getData()['lang_file_name'];

        $this->template->setTranslator(new Translator('Admin', $lang_name));
    }

    /**
     *
     */
    public function renderDefault()
    {

    }

}
