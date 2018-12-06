<?php

namespace App\Presenters;

use App\Presenters\Base\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Application\Request;

/**
 * Class Error4xxPresenter
 *
 * @package App\Presenters
 */
class Error4xxPresenter extends BasePresenter
{
    /**
     * Error4xxPresenter startup.
     */
    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()
            ->isMethod(Request::FORWARD)) {
            $this->error();
        }
    }

    /**
     * @param BadRequestException $exception
     */
    public function renderDefault(BadRequestException $exception)
    {
        $sep = DIRECTORY_SEPARATOR;
        
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . $sep . 'templates' . $sep . 'Error' . $sep . '{$exception->getCode()}.latte';
        $file = is_file($file) ? $file : __DIR__ . $sep . 'templates' . $sep . 'Error' . $sep . '4xx.latte';
        $this->template->setFile($file);
    }
}
