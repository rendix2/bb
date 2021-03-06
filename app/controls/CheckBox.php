<?php

namespace App\Controls;

use Nette;
use Nette\Forms\Controls\Checkbox as NetteCheckbox;
use Nette\Forms\Rule;
use Nette\Utils\Html;

/**
 * Check box control. Allows the user to select a true or false condition.
 *
 * @author rendix2
 * @package App\Controls
 * @property Rule $rules Description
 */
class CheckBox extends NetteCheckbox
{
    /** @var Html  wrapper element template */
    private $wrapper;


    /**
     * @param null $name
     * @param  string|object
     */
    public function __construct($name, $caption = null)
    {
        parent::__construct($caption);
        
        $this->control->type = 'checkbox';
        $this->wrapper       = Html::el();
        $this->setOption('type', 'checkbox');
    }


    /**
     * Generates control's HTML element.
     * @return Html
     */
    public function getControl()
    {
        $this->setOption('rendered', true);
        $el = clone $this->control;

        return $el->addAttributes([
            'name'             => $this->getHtmlName(),
            'id'               => $this->getHtmlId(),
            'required'         => $this->isRequired(),
            'disabled'         => $this->isDisabled(),
            'data-nette-rules' => Nette\Forms\Helpers::exportRules($this->rules) ?: null,
            'checked'          => $this->value
        ]);
    }


    /**
     * Bypasses label generation.
     *
     * @param null $caption
     *
     * @return string
     */
    public function getLabel($caption = null)
    {
        $label      = clone $this->label;
        $label->for = $this->getHtmlId();
        $label->setText($this->translate($caption === null ? $this->caption : $caption));

        return $label;
    }
}
