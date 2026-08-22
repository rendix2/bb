<?php

namespace App\ModeratorModule\Presenters;

use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Presenter;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\ModeratorModule\Presenters
 */
class IndexPresenter extends Presenter
{
    public function __construct()
    {
        parent::__construct();
    }



    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }
}
