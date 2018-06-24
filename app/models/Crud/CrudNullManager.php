<?php

namespace App\Models\Crud;

namespace App\Models\Crud;

use App\Models\Manager;
use App\Presenters\crud\CrudPresenter;
use dibi;
use Dibi\Connection;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of CrudNullManager
 *
 * @author rendi
 */
class CrudNullManager extends CrudManager implements ICrudManager
{
    private $connection;
    
    public function __construct(\Dibi\Connection $dibi)
    {
        $this->connection = $dibi;
    }
    
    public function getAll()
    {
        
    }    
    
    public function getAllCached()
    {        
    }
    
    public function getAllFluent()
    {
        return $this->connection->select('1');
    }
    
    public function getAllPairs($second){
        
    }
    
    public function getAllPairsCached($second){
        
    }
            
    public function getById($item_id)
    {
    }
    
    public function getByIds(array $item_id)
    {       
    }
    
     public function getCount(){         
     }
     
 /**
     * @return string
     */
    public function getCountCached()
    {
    }

    /**
     * @return string
     */
    private function getNameOfTableFromClass()
    {
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
    }

    /**
     * @return string
     */
    private function getPrimaryKeyQuery()
    {
    }

    /**
     * @return string
     */
    public function getTable()
    {
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
    }

    /**
     *
     */
    public function deleteCache()
    {        
    }

    /**
     * @param array $item_id
     *
     * @return Result|int
     */
    public function deleteMulti(array $item_id)
    {
    }


    /**
     * @param int       $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function update($item_id, ArrayHash $item_data)
    {
    }

    /**
     * @param array     $item_id
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function updateMulti(array $item_id, ArrayHash $item_data)
    {
    }     
}
