<?php

namespace App\ModeratorModule\Presenters\Base;

use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of ModeratorPresenter
 *
 * @author rendix2
 * @package App\ModeratorModule\Presenters\Base
 */
abstract class ModeratorPresenter extends CrudPresenter
{
    //use AuthorizationPresenter;
    
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private ITranslator $translator;

    /**
     * @return ITranslator
     */
    public function getTranslator(): ITranslator
    {
        return $this->translator;
    }

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

    /**
     * ModeratorPresenter startup.
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->getForumTranslator();
    }

    /**
     * ModeratorPresenter beforeRender.
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->translator);
    }
}
