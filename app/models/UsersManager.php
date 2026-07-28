<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use App\Settings\Avatars;
use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Tracy\Debugger;

/**
 * Description of UserManager
 *
 * @author rendix2
 * @package App\Models
 */
class UsersManager extends CrudManager
{

    /**
     * @var int
     */
    const NOT_UPLOADED = -5;


    /**
     * UsersManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     * @param Avatars    $avatars
     */
    public function __construct(
        Connection $dibi,
        IStorage   $storage,
        Avatars    $avatars
    ) {
        parent::__construct($dibi, $storage);
    }

    /**
     * @param int $user_id
     * @param string $key
     *
     * @return mixed
     */
    public function canBeActivated($user_id, $key)
    {
        return $this->dibi
            ->select('1')
            ->from($this->getTable())
            ->where('[' . $this->getPrimaryKey() . '] = %i', $user_id)
            ->where('[user_activation_key] = %s', $key)
            ->where('[user_active] = %i', 0)
            ->fetchSingle();
    }

    /**
     * @param string $user_name
     *
     * @return Row[]
     */
    public function findLikeByUserName($user_name)
    {
        return $this->getAllFluent()
            ->where('[user_name] LIKE %~like~', $user_name)
            ->fetchAll();
    }
}
