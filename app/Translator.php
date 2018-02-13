<?php

namespace App;

use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 * Description of Translator
 *
 * @author rendi
 */
class Translator implements ITranslator {

    private $module;
    private $lang;
    private $tr;

    public function __construct($module, $lang) {
        $this->module = $module;
        $this->lang = $lang;
        $this->tr = parse_ini_file('c:/xampp/htdocs/bb/App/' . $this->module . 'Module/languages/' . $this->lang . '.ini');
    }

    public function __destruct() {
        $this->tr = null;
        $this->lang = null;
        $this->module = null;
    }

    public function translate($message, $count = null) {
        if (!array_key_exists($message, $this->tr)) {
            throw new InvalidArgumentException("'{$message}'" . ' in language ' . $this->lang . ' in '.$this->module.' is missing.');
        }

        return $this->tr[$message];
    }

}
