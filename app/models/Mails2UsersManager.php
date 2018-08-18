<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Mails2UsersManager
 *
 * @author rendix2
 */
class Mails2UsersManager extends MNManager
{
    /**
     *
     * @param Connection $dibi
     * @param MailsManager $left
     * @param UsersManager $right
     */
    public function __construct(Connection $dibi, MailsManager $left, UsersManager $right)
    {
        parent::__construct($dibi, $left, $right);
    }
}
