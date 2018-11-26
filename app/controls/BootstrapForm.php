<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 6. 2017
 * Time: 11:41
 */

namespace App\Controls;

use App\Controls\CheckBox as MyCheckBox;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextBase;

/**
 * Class BootstrapForm
 *
 * @author rendix2
 * @method type addReCaptcha($name, $label, $error) Description
 * @package App\Controls
 *
 */
class BootstrapForm extends Form
{
    /**
     * column count
     *
     * @var int $columnCount count of columns
     * @api
     */
    private $columnCount;
    
    /**
     * type of column
     *
     * @var string $columnType column type like sm/lg/xs....
     * @api
     */
    private $columnType;
    
    /**
     * columns in row
     * like sm-8 ...
     *
     * @var int $labelColumnCount
     * @api
     */
    private $labelColumnCount;
    
    /**
     *
     * @var bool $ajax
     */
    private $ajax;

    /**
     * BootstrapForm constructor.
     *
     * @param int             $columnCount      column count
     * @param string          $columnType       column type
     * @param int             $labelColumnCount label column count
     * @param bool            $ajax             ajax
     * @param IContainer|null $parent           parent
     * @param null            $name             name
     *
     * @api
     */
    public function __construct(
        $columnCount       = 6,
        $columnType        = 'sm',
        $labelColumnCount  = 6,
        $ajax              = false,
        IContainer $parent = null,
        $name              = null
    ) {
        parent::__construct(
            $parent,
            $name
        );
        $this->addProtection('Try it again.');

        $this->columnCount      = $columnCount;
        $this->columnType       = $columnType;
        $this->labelColumnCount = $labelColumnCount;
        $this->ajax             = $ajax;
    }
    
    /**
     * BootstrapForm destruct.
     *
     * @api
     */
    public function __destruct()
    {
        $this->columnCount = null;
        $this->columnType = null;
        $this->labelColumnCount = null;
        $this->ajax = null;
    }

    /**
     * @return BootstrapForm
     */
    public static function create()
    {
        return new BootstrapForm();
    }
    
    /**
     *
     * @return BootstrapForm
     */
    public static function createAjax()
    {
        return self::create()->setAjax(true);
    }
    
    /**
     *
     * @param int $count
     *
     * @return BootstrapForm
     */
    public function setColumnCount($count = 6)
    {
        $this->columnCount = $count;
        
        return $this;
    }
    
    /**
     *
     * @param string $type
     *
     * @return BootstrapForm
     */
    public function setColumnType($type = 'sm')
    {
        $this->columnType = $type;
        
        return $this;
    }

    /**
     *
     * @param int $count
     *
     * @return BootstrapForm
     */
    public function setLabelColumnCount($count = 6)
    {
        $this->labelColumnCount = $count;
        
        return $this;
    }
    
    /**
     *
     * @param bool $ajax
     *
     * @return BootstrapForm
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
        
        return $this;
    }
    
    /**
     * @param string      $name
     * @param string|null $caption
     *
     * @return MyCheckBox
     */
    public function addCheckbox($name, $caption = null)
    {
        return $this[$name] = new MyCheckBox(
            $name,
            $caption
        );
    }

    /**
     * adds email input
     *
     * @param string      $name  name
     * @param string|null $label label
     *
     * @return TextBase
     * @api
     */
    public function addEmail($name, $label = null)
    {
        return parent::addEmail($name, $label)->setType(Form::EMAIL)->addRule(Form::EMAIL, 'Not a valid email');
    }

    /**
     * adds float
     *
     * @param string      $name  name
     * @param string|null $label label
     *
     * @return TextBase
     * @api
     */
    public function addFloat($name, $label = null)
    {
        return $this->addText($name, $label)->setRequired(false)->addRule(Form::FLOAT);
    }

    /**
     * adds textarea with html editor
     *
     * @param string $name  name
     * @param null   $label label
     * @param null   $cols  cols
     * @param null   $rows  rows
     *
     *
     * @return TextArea
     * @api
     */
    public function addTextAreaHtml($name, $label = null, $cols = null, $rows = null)
    {
        return $this->addTextArea($name, $label, $cols, $rows)->setAttribute('class', 'mceEditor');
    }

    /**
     * this method that do all that black magic!
     *
     * @api
     */
    public function useBootStrap()
    {
        $renderer = $this->getRenderer();

        $renderer->wrappers['controls']['container']     = null;
        $renderer->wrappers['control']['description']    = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        $renderer->wrappers['control']['container']      = 'div class=col-' . $this->columnType . '-' . $this->columnCount;
        $renderer->wrappers['pair']['container']         = 'div class="form-group row"';
        $renderer->wrappers['pair']['.error']            = 'has-error';
        $renderer->wrappers['label']['container']        = 'div class="col-' . $this->columnType . '-' . $this->labelColumnCount . ' control-label"';
        $renderer->wrappers['label']['']                 = '';

        // make form and controls compatible with Twitter Bootstrap
        $ajax = $this->ajax ? 'ajax ' : '';
        
        $this->getElementPrototype()->class($ajax. 'form-horizontal');

        foreach ($this->getControls() as $control) {
            // add some class for label!;
            $control->getLabelPrototype()
                ->setAttribute('class', 'control-label col-' . $this->columnType . '-' . $this->labelColumnCount);

            if ($control instanceof Button) {
                if ($control->getControlPrototype()->getAttribute('class') === null) {
                    $control->getControlPrototype()->addClass('btn btn-primary');
                }
            } elseif ($control instanceof TextBase || $control instanceof SelectBox || $control instanceof MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof CheckboxList || $control instanceof RadioList) {
                // add class with pt-0 for checkbox
                $control->getLabelPrototype()
                    ->setAttribute('class', 'control-label col-' . $this->columnType . '-' . $this->labelColumnCount . ' pt-0');
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
    }

    /**
     * make this form Bootstrap-like
     *
     * @api
     */
    public function beforeRender()
    {
        parent::beforeRender();
        $this->useBootStrap();
    }
}
