<?php

namespace App\Services;

use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use Dibi\Connection;
use Zebra_Mptt;

/**
 * Description of MtppFactory
 *
 * @author rendi
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
     *
     * @param Connection        $dibi
     * @param CategoriesManager $categoriesManager
     * @param ForumsManager     $forumsManager
     */
    public function __construct(Connection $dibi, CategoriesManager $categoriesManager, ForumsManager $forumsManager)
    {
        $this->categoriesManager = $categoriesManager;
        $this->forumsManager     = $forumsManager;
        $this->dibi              = $dibi;
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
            $this->categoriesManager->getTitle(),
            $this->categoriesManager->getLeft(),
            $this->categoriesManager->getRight(),
            $this->categoriesManager->getParent()
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
            $this->forumsManager->getTitle(),
            $this->forumsManager->getLeft(),
            $this->forumsManager->getRight(),
            $this->forumsManager->getParent()
        );
    }
    
}
