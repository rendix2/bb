<?php

namespace App\Models;

use dibi;
use Dibi\Row;
use Nette\Caching\Cache;
use Dibi\Connection;
use Zebra_Mptt;

/**
 * Class CategoriesManager
 *
 * @package App\Models
 */
class CategoriesManager extends Crud\CrudManager
{
    /**
     * @var Zebra_Mptt $mptt
     */
    private $mptt;

    /**
     * CategoriesManager constructor.
     *
     * @param Connection $dibi
     */
    public function __construct(Connection $dibi)
    {
        parent::__construct($dibi);
        
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
    public function getByForumId($forum_id)
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
}
