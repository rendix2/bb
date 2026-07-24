<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Row;
use Exception;
use Nette\Caching\IStorage;
use Zebra_Mptt;

/**
 * Description of ForumManager
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]

class ForumManager extends CrudManager
{
    /**
     * ForumsManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     *
     * @throws Exception
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        Connection $dibi,
        IStorage $storage
    )
    {
        parent::__construct($dibi, $storage);
    }

    /**
     * @param int $category_id
     *
     * @return Fluent
     */
    public function getFluentByCategory(int $category_id)
    {
        return $this->getAllFluent()
            ->where('[forum_category_id] = %i', $category_id);
    }

    /**
     *
     * @param int $category_id
     *
     * @return Row[]
     */
    public function getAllByCategory(int $category_id)
    {
        return $this->getFluentByCategory($category_id)
            ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return Row[]
     */
    public function getAllByParent(int $forum_id): array
    {
        return $this->getAllFluent()
            ->where('[forum_parent_id] = %i', $forum_id)
            ->fetchAll();
    }

    /**
     * @param iterable $forums
     * @param int $forum_parent_id
     *
     * @return array
     */
    public function createForums($forums, $forum_parent_id): array
    {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->forum_parent_id === $forum_parent_id) {
                $result[$forum->forum_id] = $forum;
                $result[$forum->forum_id]['childs'] = $this->createForums(
                    $forums,
                    $forum->forum_id
                );
            }
        }

        return $result;
    }

    /**
     * @param int $forumId
     * @return ForumEntity[]
     */
    public function getBreadCrumb(int $forumId): array
    {
        $forum = $this->em
            ->getRepository(ForumEntity::class)
            ->find($forumId);

        $crumbs = [];

        $current = $forum;
        while ($current !== null) {
            array_unshift($crumbs, [
                'link'   => 'Forum:default',
                'params' => [
                    'category_id' => $current->getCategory()->getId(),
                    'forum_id'    => $current->getId()
                ],
                'text'   => $current->getName(),
                't'      => 0
            ]);

            $current = $current->getParent();
        }

        return $crumbs;
    }
}
