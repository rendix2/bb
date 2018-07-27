<?php

namespace App\Settings;

/**
 * Description of Email
 *
 * @author rendi
 */
class Email
{
    
    /**
     * @var array $mail
     */
    private $mail;
    
    /**
     * @param array $mail
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }
    
    /**
     *
     * @return array
     */
    public function getMail()
    {
        return $this->mail;
    }
}
