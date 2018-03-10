<?php

namespace App;

use App\Controls\AppDir;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 * Description of Translator
 *
 * @author rendi
 */
class Translator implements ITranslator
{
    /**
     * @var string $module
     */
    private $module;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var array|bool $tr
     */
    private $tr;

    /**
     * Translator constructor.
     *
     * @param AppDir $appDir
     * @param        $module
     * @param        $lang
     */
    public function __construct(AppDir $appDir, $module, $lang)
    {
        $this->module = $module;
        $this->lang   = $lang;
        $separator    = DIRECTORY_SEPARATOR;

        $this->tr = parse_ini_file(
            $appDir->appDir . $separator . $this->module . 'Module' . $separator . 'languages' . $separator . $this->lang . '.ini'
        );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->tr     = null;
        $this->lang   = null;
        $this->module = null;
    }

    /**
     * @param      $message
     * @param null $count
     *
     * @return mixed
     */
    public function translate($message, $count = null)
    {
        if ( $message === null ){
            return '';
        }
        
        if (!array_key_exists(
            $message,
            $this->tr
        )) {
            throw new InvalidArgumentException(
                "'{$message}'" . ' in language ' . $this->lang . ' in ' . $this->module . ' is missing.'
            );
        }

        return $this->tr[$message];
    }
}
