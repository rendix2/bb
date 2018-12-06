<?php

namespace App\Services;

use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use Dibi\Connection;
use Zebra_Mptt;

/**
 * Description of MtppFactory
 *
 * @author rendix2
 */
class MpttFactory
{
    
    /**
     *
     * @var CategoriesManager $categoriesManager
     */
    private $categoriesManager;

    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var Connection $dibi
     */
    private $dibi;

    /**
     * MpttFactory constructor.
     *
     * @param Connection        $dibi
     * @param CategoriesManager $categoriesManager
     * @param ForumsManager     $forumsManager
     */
    public function __construct(
        Connection        $dibi,
        CategoriesManager $categoriesManager,
        ForumsManager     $forumsManager
    ) {
        $this->categoriesManager = $categoriesManager;
        $this->forumsManager     = $forumsManager;
        $this->dibi              = $dibi;
    }
    
    /**
     * MpttFactory destructor.
     */
    public function __destruct()
    {
        $this->categoriesManager = null;
        $this->forumsManager     = null;
        $this->dibi              = null;
    }

    /**
     *
     * @return Zebra_Mptt
     */
    public function getCategories()
    {
        return new Zebra_Mptt(
            $this->dibi,
            $this->categoriesManager->getTable(),
            $this->categoriesManager->getPrimaryKey(),
            'category_name',
            'category_left',
            'category_right',
            'category_parent_id'
        );
    }
    
    /**
     *
     * @return Zebra_Mptt
     */
    public function getForum()
    {
        return new Zebra_Mptt(
            $this->dibi,
            $this->forumsManager->getTable(),
            $this->forumsManager->getPrimaryKey(),
            'forum_name',
            'forum_left',
            'forum_right',
            'forum_parent_id'
        );
    }
}
