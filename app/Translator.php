<?php

namespace App;

use App\Settings\AppDir;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 * Description of Translator
 *
 * @author rendix2
 * @package App
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
     * @param string $module
     * @param string $lang
     */
    public function __construct(AppDir $appDir, $module, $lang)
    {
        $this->module = $module;
        $this->lang   = $lang;
        $sep          = DIRECTORY_SEPARATOR;
        $this->tr     = parse_ini_file($appDir->get() . $sep . $this->module . 'Module' . $sep . 'languages' . $sep . $this->lang . '.ini');
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
     * @param string $message
     * @param null   $count
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function translate($message, $count = null)
    {
        if ($message === null) {
            return '';
        }
              
        if (!array_key_exists($message, $this->tr)) {
            throw new InvalidArgumentException("'{$message}'" . ' in language ' . $this->lang . ' in ' . $this->module . 'Module is missing.');
        }

        return $this->tr[$message];
    }
}
