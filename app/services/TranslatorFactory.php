<?php

namespace App\Services;

use App\Controls\AppDir;
use App\Translator;
use Nette\Security\User;

/**
 * Description of TranslatorFactory
 *
 * @author rendi
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
     * TranslatorFactory constructor.
     *
     * @param User $user
     * @param AppDir $appDir
     */
    public function __construct(User $user, AppDir $appDir)
    {
        $this->user = $user;
        $this->appDir = $appDir;
        
        $this->setLang();
    }

    /**
     *
     */
    private function setLang()
    {
        $identity = $this->user->getIdentity();
        $lang = '';
        
        if ($identity) {
            $lang = $identity->getData()['lang_file_name'];
        } else {
            $lang = 'czech';
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
