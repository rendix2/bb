<?php

namespace App\ModeratorModule\Presenters;

use App\Models\PostsManager;
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
    
    /**
     * IndexPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
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

    /**
     * IndexPresenter checkRequirements
     *
     * @param $element
     */
    public function checkRequirements($element)
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
        $this->template->setTranslator($this->translator);
    }
}
