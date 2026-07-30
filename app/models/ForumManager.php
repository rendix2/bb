<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\DriverException;
use Nette\Caching\IStorage;

/**
 * Description of ForumManager
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]
class ForumManager extends CrudManager
{
    public function __construct(
        Connection $dibi,
        IStorage $storage
    )
    {
        parent::__construct($dibi, $storage);
    }

    /**
     * @param ForumEntity[] $forums
     * @param int $forum_parent_id
     *
     * @return array
     */
    public function createForums(array $forums, int $forum_parent_id): array
    {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->parent->id === $forum_parent_id) {
                $result[$forum->id] = $forum;
                $result[$forum->id]['childs'] = $this->createForums(
                    $forums,
                    $forum->id
                );
            }
        }

        return $result;
    }
}
