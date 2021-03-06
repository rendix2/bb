<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;
use Zebra_Mptt;

/**
 * Class CategoriesManager
 *
 * @author rendix2
 * @package App\Models
 */
class CategoriesManager extends CrudManager
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
    public function __construct(Connection $dibi, IStorage $storage)
    {
        parent::__construct($dibi, $storage);

        $this->mptt = new Zebra_Mptt(
            $dibi,
            $this->getTable(),
            $this->getPrimaryKey(),
            'category_name',
            'category_left',
            'category_right',
            'category_parent_id'
        );
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
     * @return array
     */
    public function getAllActiveCategories()
    {
        return $this->getAllFluent()
            ->where('[category_active] = %i', 1)
            ->orderBy('category_order', dibi::ASC)
            ->fetchAll();
    }

    /**
     * @return array|mixed
     */
    public function getActiveCategoriesCached()
    {
        $key    = 'ActiveCategories';
        $cached = $this->managerCache->load($key);

        if (!$cached) {
            $this->managerCache->save($key, $cached = $this->getAllActiveCategories());
        }

        return $cached;
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getByForumJoinedForum($forum_id)
    {
        return $this->getAllFluent()
            ->as('c')
            ->leftJoin(self::FORUM_TABLE)
            ->as('f')
            ->on('[f.forum_category_id] = [c.category_id]')
            ->where('[f.forum_id] = %i', $forum_id)
            ->fetch();
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
     * @param int  $category_id
     * @param int  $target_category_id
     * @param bool $position
     *
     * @return bool
     */
    public function move($category_id, $target_category_id, $position = false)
    {
        $this->deleteCache();

        return $this->mptt->move($category_id, $target_category_id, $position);
    }

    /**
     * @param int $category_id
     *
     * @return array
     */
    public function getBreadCrumb($category_id)
    {
        $categories = $this->mptt->getBreadCrumb($category_id);
        
        $breadCrumbCategory = [];
        
        foreach ($categories as $category) {
            $tmp = [];
            $tmp['link']   = ':Forum:Category:default';
            $tmp['params'] = ['category_id' => $category->category_id];
            $tmp['text']   = $category->category_name;
            $tmp['t']      = 0;
            
            $breadCrumbCategory[] = $tmp;
        }
        
        return $breadCrumbCategory;
    }
}
