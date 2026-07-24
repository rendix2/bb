<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;
use Zebra_Mptt;

/**
 * Class CategoryManager
 *
 * @author rendix2
 * @package App\Models
 */
#[\Nette\Application\Attributes\Deprecated]
class CategoryManager extends CrudManager
{
    /**
     * @var Zebra_Mptt $mptt
     */
    private $mptt;

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
     * CategoriesManager destructor.
     */
    public function __destruct()
    {
        $this->mptt = null;
        
        parent::__destruct();
    }

    /**
     *
     * @return Zebra_Mptt
     */
    public function getMptt()
    {
        return $this->mptt;
    }

    /**
     * @param int $category_id
     *
     * @return Row[]
     */
    public function getByParent($category_id)
    {
        return $this->getAllFluent()
            ->where('[category_parent_id] = %i', $category_id)
            ->fetchAll();
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
