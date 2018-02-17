<?php

namespace App\Presenters\Base;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    private $wwwDir;


    const FLASH_MESSAGE_SUCCESS = 'success';

    const FLASH_MESSAGE_DANGER = 'danger';

    const FLASH_MESSAGE_WARNING = 'warning';

    const FLASH_MESSAGE_INFO = 'info';

    public function startup()
    {
        parent::startup();
    }
    
}