<?php

namespace App\Models;

/**
 * Description of LanguageManager
 *
 * @author rendi
 */
class LanguagesManager extends Crud\CrudManager {

    public function getAllForSelect() {
        return $this->dibi->select('*')->from($this->getTable())->fetchPairs('lang_id', 'lang_name');
    }

}
