<?php

namespace App\ModeratorModule\Presenters\Base;

use App\Authorization\AuthorizationPresenter;
use App\Models\ModeratorsManager;
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
    use AuthorizationPresenter;
    
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private $translator;
    
    /**
     *
     * @var ModeratorsManager $moderatorsManager
     * @inject
     */
    public $moderatorsManager;

    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * ModeratorPresenter checkRequirements
     *
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $this->user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     * ModeratorPresenter startup.
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->createForumTranslatorFactory();
    }

    /**
     * ModeratorPresenter beforeRender.
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translator);
    }
}
