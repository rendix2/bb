<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of Mails2UsersManager
 *
 * @author rendix2
 * @package App\Models
 */
class Mails2UsersManager extends MNManager
{
    /**
     * Mails2UsersManager constructor.
     *
     * @param Connection   $dibi
     * @param IStorage     $storage
     * @param MailsManager $left
     * @param UsersManager $right
     */
    public function __construct(Connection $dibi, IStorage $storage, MailsManager $left, UsersManager $right)
    {
        parent::__construct($dibi, $storage, $left, $right);
    }
}
