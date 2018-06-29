<?php

namespace App\ModeratorModule;

use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of ModeratorPresenter
 *
 * @author rendi
 */
abstract class ModeratorPresenter extends CrudPresenter
{
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private $translator;
    
    /**
     * @var \App\Services\TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;

    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    public function startup()
    {
        parent::startup();

        if (!$this->getUser()
            ->isInRole('moderator')) {
            $this->error('You are not in moderator role!s');
        }
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translator);
    }
}
