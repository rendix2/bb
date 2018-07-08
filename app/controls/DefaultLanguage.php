<?php

namespace App\Controls;

/**
 * Description of DefaultLanguage
 *
 * @author rendi
 */
class DefaultLanguage {

    /**
     *
     * @var string $defaultLanguage
     */
    private $defaultLanguage;
    
    /**
     * 
     * @param string $defaultLanguage
     */
    public function __construct($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
        
    }
    
    /**
     * 
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }
}
