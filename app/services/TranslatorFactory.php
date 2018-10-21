<?php

namespace App\Services;

use App\Settings\AppDir;
use App\Settings\DefaultLanguage;
use App\Translator;
use Nette\Security\User;

/**
 * Description of TranslatorFactory
 *
 * @author rendix2
 */
class TranslatorFactory
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * @var string$lang
     */
    private $lang;

    /**
     * @var AppDir $appDir
     */
    private $appDir;
    
    /**
     * @var DefaultLanguage $defaultLanguage
     */
    private $defaultLanguage;

    /**
     * TranslatorFactory constructor.
     *
     * @param User            $user
     * @param AppDir          $appDir
     * @param DefaultLanguage $defaultLanguage
     */
    public function __construct(User $user, AppDir $appDir, DefaultLanguage $defaultLanguage)
    {
        $this->user            = $user;
        $this->appDir          = $appDir;
        $this->defaultLanguage = $defaultLanguage;
        
        $this->setLang();
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->user            = null;
        $this->lang            = null;
        $this->appDir          = null;
        $this->defaultLanguage = null;
    }

    /**
     * sets default lang
     */
    private function setLang()
    {
        $identity = $this->user->getIdentity();
        $lang = '';
        
        if ($identity) {
            $lang = $identity->getData()['lang_file_name'];
        } else {
            $lang = $this->defaultLanguage->get();
        }
        
        $this->lang = $lang;
    }

    /**
     * @return Translator
     */
    public function adminTranslatorFactory()
    {
        return new Translator($this->appDir, 'Admin', $this->lang);
    }

    /**
     * @return Translator
     */
    public function forumTranslatorFactory()
    {
        return new Translator($this->appDir, 'Forum', $this->lang);
    }
}
