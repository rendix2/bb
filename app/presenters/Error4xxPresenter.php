<?php

namespace App\Presenters;

use App\Presenters\Base\BasePresenter;
use Nette;
use Nette\Application\BadRequestException;

/**
 * Class Error4xxPresenter
 *
 * @package App\Presenters
 */
class Error4xxPresenter extends BasePresenter
{
    /**
     *
     */
    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()
            ->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }

    /**
     * @param BadRequestException $exception
     */
    public function renderDefault(BadRequestException $exception)
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $file = is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte';
        $this->template->setFile($file);
    }
}
