<?php

namespace App\ModeratorModule\Presenters;

use App\Presenters\Base\BasePresenter;
use Nette\Localization\ITranslator;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\ModeratorModule\Presenters
 */
class IndexPresenter extends BasePresenter
{
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private $translator;
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * IndexPresenter destructor.
     */
    public function __destruct()
    {
        $this->translator = null;

        parent::__destruct();
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     * IndexPresenter startup.
     */
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->getForumTranslator();
        $this->getTemplate()->setTranslator($this->translator);
    }
}
