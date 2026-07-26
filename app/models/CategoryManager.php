<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Nette\Caching\IStorage;

#[\Nette\Application\Attributes\Deprecated]
class CategoryManager extends CrudManager
{
    /**
     * CategoriesManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
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
     * @param int $categoryId
     * @return array
     */
    public function getBreadCrumb(int $categoryId): array
    {
        $category = $this->em
            ->getRepository(CategoryEntity::class)
            ->find($categoryId);

        if (!$category) {
            return [];
        }

        $breadCrumbCategory = [];
        $current = $category;

        while ($current !== null) {
            array_unshift($breadCrumbCategory, [
                'link'   => ':Forum:Category:default',
                'params' => ['category_id' => $current->getId()],
                'text'   => $current->getName(),
                't'      => 0,
            ]);

            $current = $current->getParent();
        }

        return $breadCrumbCategory;
    }
}
