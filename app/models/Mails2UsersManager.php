<?php

namespace App\Models;

/**
 * Description of Mails2UsersManager
 *
 * @author rendi
 */
class Mails2UsersManager extends MNManager
{
    /**
     * 
     * @param \Dibi\Connection $dibi
     * @param \App\Models\MailsManager $left
     * @param \App\Models\UsersManager $right
     */
    public function __construct(\Dibi\Connection $dibi, MailsManager $left, UsersManager $right) {
        parent::__construct($dibi, $left, $right);
    }    
}
