<?php

namespace App\Services;

use App\Settings\AppDir;
use App\Settings\DefaultLanguage;
use App\Translator;
use Nette\Security\User;
use Nette\Localization\ITranslator;

/**
 * Description of TranslatorFactory
 *
 * @author rendix2
 * @package App\Services
 */
class TranslatorFactory
{
    /**
     * @var User $user
     */
    private User $user;

    /**
     * @var string$lang
     */
    private string $lang;

    /**
     * @var AppDir $appDir
     */
    private AppDir $appDir;
    
    /**
     * @var DefaultLanguage $defaultLanguage
     */
    private DefaultLanguage $defaultLanguage;

    /**
     * TranslatorFactory constructor.
     *
     * @param User            $user
     * @param AppDir          $appDir
     * @param DefaultLanguage $defaultLanguage
     */
    public function __construct(
        User            $user,
        AppDir          $appDir,
        DefaultLanguage $defaultLanguage
    ) {
        $this->user            = $user;
        $this->appDir          = $appDir;
        $this->defaultLanguage = $defaultLanguage;
        
        $this->setLang();
    }

    /**
     * sets default lang
     */
    private function setLang(): void
    {
        if ($this->user->isLoggedIn()) {
            if (isset($this->user->getIdentity()?->getData()['lang_file_name'])) {
                $lang = $this->user->getIdentity()?->getData()['lang_file_name'];
            } else {
                $lang = $this->defaultLanguage->get();
            }
        } else {
            $lang = $this->defaultLanguage->get();
        }
        
        $this->lang = $lang;
    }

    /**
     * @return Translator
     */
    public function getAdminTranslator(): Translator
    {
        return new Translator($this->appDir, 'Admin', $this->lang);
    }

    /**
     * @return Translator
     */
    public function getForumTranslator(): Translator
    {
        return new Translator($this->appDir, 'Forum', $this->lang);
    }
}
