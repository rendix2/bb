<?php

namespace App\Services;

/**
 * Description of MtppFactory
 *
 * @author rendi
 */
class MpttFactory
{
    
    /**
     *
     * @var \App\Models\CategoriesManager $categoriesManager
     */
    private $categoriesManager;

    /**
     *
     * @var \App\Models\ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var \Dibi\Connection $dibi
     */
    private $dibi;

    /**
     * 
     * @param \Dibi\Connection $dibi
     * @param \App\Models\CategoriesManager $categoriesManager
     * @param \App\Models\ForumsManager $forumsManager
     */
    public function __construct(\Dibi\Connection $dibi, \App\Models\CategoriesManager $categoriesManager, \App\Models\ForumsManager $forumsManager)
    {
        $this->categoriesManager = $categoriesManager;
        $this->forumsManager     = $forumsManager;
        $this->dibi              = $dibi;
    }
    
    /**
     * 
     * @return \App\Services\Zebra_Mptt
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
     * @return \App\Services\Zebra_Mptt
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
