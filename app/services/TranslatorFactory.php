<?php

namespace App\Services;

/**
 * Description of TranslatorFactory
 *
 * @author rendi
 */
class TranslatorFactory {
    
    
    private $user;
    
    private $lang;
    
    private $appDir;
    
    
    public function __construct(\Nette\Security\User $user, \App\Controls\AppDir $appDir)
    {
        $this->user = $user;
        $this->appDir = $appDir;
        
        $this->setLang();
    }
    
    private function setLang()
    {
        $identity = $this->user->getIdentity();
        $lang = '';
        
        if ($identity){
            $lang = $identity->getData()['lang_file_name'];
        } else {
            $lang = 'czech';
        }
        
        $this->lang = $lang;
    }

    public function adminTranslatorFactory()
    {
        return new \App\Translator($this->appDir, 'Admin', $this->lang);
    }
    
    public function forumTranslatorFactory()
    {
        return new \App\Translator($this->appDir, 'Forum', $this->lang);
    }    
    
}
