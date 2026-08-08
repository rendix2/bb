<?php

namespace App\ModeratorModule\Presenters\Base;

use App\Presenters\crud\CrudPresenter;

/**
 * Description of ModeratorPresenter
 *
 * @author rendix2
 * @package App\ModeratorModule\Presenters\Base
 */
abstract class ModeratorPresenter extends CrudPresenter
{
    /**
     * ModeratorPresenter checkRequirements
     *
     * @param mixed $element
     */
    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }


}
