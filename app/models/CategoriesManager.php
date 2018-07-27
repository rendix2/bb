<?php

namespace App\Models;

use dibi;
use Dibi\Row;
use Dibi\Connection;
use Zebra_Mptt;
use Nette\Caching\IStorage;

/**
 * Class CategoriesManager
 *
 * @package App\Models
 */
class CategoriesManager extends Crud\CrudManager implements MpttTable
{
    /**
     * @var \Zebra_Mptt $mptt
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
            $this->getTitle(),
            $this->getLeft(),
            $this->getRight(),
            $this->getParent()
        );
    }
    
    /**
     * @return array
     */
    public function getActiveCategories()
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
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
            $this->managerCache->save($key, $cached = $this->getActiveCategories());
        }

        return $cached;
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getByForum($forum_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
            ->as('c')
            ->leftJoin(self::FORUM_TABLE)
            ->as('f')
            ->on('[f.forum_category_id] = [c.category_id]')
            ->where('[f.forum_id] = %i', $forum_id)
            ->fetch();
    }

    /**
     *
     */
    public function move()
    {
        //$this->mptt->move(1, 2);
        //$this->mptt->add(0, 'TEST CAT');
        //$this->mptt->add(0, 'cat 2');
        //$this->mptt->add(0, 'CAT 3');
        
        //$this->mptt->move(1, 2);
        \Tracy\Debugger::barDump($this->mptt->get_tree());
    }

    /**
     * MpttTable interface
     */
    
    public function getLeft()
    {
        return 'category_left';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'category_parent_id';
    }

    /**
     * @return string
     */
    public function getRight()
    {
        return 'category_right';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'category_name';
    }
}
