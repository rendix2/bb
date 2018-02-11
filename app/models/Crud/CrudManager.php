<?php

namespace App\Models\Crud;

use Nette\Caching\Cache;

/**
 * Description of CrudManager
 *
 * @author rendi
 */
abstract class CrudManager extends \App\Models\Manager {

    private $table;
    private $primaryKey;
    private $cache;
    
    public $storage;

    public function __construct(\Dibi\Connection $dibi) {
        parent::__construct($dibi);

        $this->table = $this->getNameOfTableFromClass();
    }

    public function factory(\Nette\Caching\IStorage $storage) {
        $this->cache = new Cache($storage, \App\Presenters\crud\CrudPresenter::CACHE_KEY_PRIMARY_KEY);
        $this->primaryKey = $this->getPrimaryKeyQuery();
        $this->storage = $storage;
    }

    public function getCache() {
        return $this->cache;
    }
    
    public function getStorage(){
        return $this->storage;
    }

    private function getNameOfTableFromClass() {
        $explodedName = explode('\\', str_replace('Manager', '', get_class($this)));
        $count = count($explodedName);

        return mb_strtolower($explodedName[$count - 1]);
    }

    private function getPrimaryKeyQuery() {

        $cachedPrimaryKey = $this->cache->load('primaryKey_' . $this->table);

        if (!$cachedPrimaryKey) {
            $data = $this->dibi->select('COLUMN_NAME')->from('information_schema.COLUMNS')->where('[TABLE_NAME] = %s', $this->table)->where('COLUMN_KEY = %s', 'PRI')->fetchSingle();

            $this->cache->save('primaryKey_' . $this->table, $cachedPrimaryKey = $data, [
                Cache::EXPIRE => '24 hours',
            ]);
        }

        return $cachedPrimaryKey;
    }

    public function getTable() {
        return $this->table;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    public function getCount() {
        return $this->dibi->select('COUNT(*)')->from($this->table)->fetchSingle();
    }

    public function add(\Nette\Utils\ArrayHash $item_data) {
        return $this->dibi->insert($this->table, $item_data)->execute(\dibi::IDENTIFIER);
    }

    public function delete($item_id) {
        return $this->dibi->delete($this->table)->where('[' . $this->primaryKey . '] = %i', $item_id)->execute(\dibi::AFFECTED_ROWS);
    }

    public function update($item_id, \Nette\Utils\ArrayHash $item_data) {
        return $this->dibi->update($this->table, $item_data)->where('[' . $this->primaryKey . '] = %i', $item_id)->execute(\dibi::AFFECTED_ROWS);
    }

    public function getAllFluent() {
        return $this->dibi->select('*')->from($this->table);
    }

    public function getAll() {
        return $this->dibi->select('*')->from($this->table)->fetchAll();
    }
    
    public function getAllCached(){
        $cache = new Cache($this->getStorage(), $this->getTable());
        
        $cached = $cache->load('data');
        
        if (!$cached){
            $cache->save('data', $cached = $this->getAll(),[
                Cache::EXPIRE => '24 hours',
            ]);
        }
        
        return $cached;
    }

    public function getById($item_id) {
        return $this->dibi->select('*')->from($this->table)->where('[' . $this->primaryKey . '] = %i', $item_id)->fetch();
    }

}
