<?php

namespace App\Controls;

/**
 * Description of DefaultLanguage
 *
 * @author rendi
 */
class DefaultLanguage {

    private $defaultLanguage;
    
    public function __construct($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
        
    }
    
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }
}
