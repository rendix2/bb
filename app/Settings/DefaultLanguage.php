<?php

namespace App\Settings;

/**
 * Description of DefaultLanguage
 *
 * @author rendix2
 */
class DefaultLanguage
{

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
