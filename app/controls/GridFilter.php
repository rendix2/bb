<?php

namespace App\Controls;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of GridFilter
 *
 * @author rendi
 */
class GridFilter extends Control
{
    /**
     * @var string
     */
    const TEXT_EQUAL = 'teq';
    
    /**
     * @var string
     */
    const TEXT_LIKE = 'tl';
    
    /**
     * @var string
     */
    const INT_EQUAL = 'i';

    /**
     * @var string
     */
    const NOTHING = 'empty';

    /**
     * @var string
     */
    const FROM_TO_INT = 'FTI';
    
    /**
     * @var string
     */
    const CHECKBOX_LIST = 'ch';
    
    /**
     * @var string datetime
     */
    const DATE_TIME = 'date';

    /**
     * @var array $whereColumns
     */
    private $whereColumns;

    /**
     * @var BootstrapForm $form
     */
    private $form;

    /**
     * @var array $type
     */
    private $type;

    /**
     * @var Session $session
     */
    private $session;
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * GridFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->form    = BootstrapForm::create();
        $this->type    = [];
    }

    /**
     * @param ITranslator $translator
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Session $session
     */
    public function factory(Session $session)
    {
        $this->session = $session;
    }    

    /**
     * @return array
     */
    public function getOrderBy()
    {
        $orderBy = [];

        foreach ($this->getParameters() as $param => $value) {
            $param = str_replace('sort_', '', $param);
            $param = $this->checkFTI($param);

            $orderBy[$param] = ($value === 'ASC' || $value === 'DESC') ? $value : null;
        }

        return $orderBy;
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        if ($this->whereColumns) {
            return $this->whereColumns;
        } else {
            // get from session
            $where = [];

            foreach ($this->type as $col => $val) {
                \Tracy\Debugger::barDump($val, $col);
                
                if ( ($val['strint'] === '%in' && count($this->session->getSection($col)->value))
                    || ($val['strint'] === '%i' && is_numeric($this->session->getSection($col)->value)) 
                    || ( ($val['strint'] === '%s' || $val['strint'] == '%~like~')
                        && is_string($this->session->getSection($col)->value)
                        && mb_strlen($this->session->getSection($col)->value) >= 1
                    ) 
                    || $val['type'] === 'date'        
                    && $col !== self::NOTHING) {
                    \Tracy\Debugger::barDump('proslo');
                    
                    $columnName = $this->checkFTI($col);

                    if ($val['type'] === 'date') {
                        if (!$this->session->getSection($col)->value) {
                            continue;
                        }
                        
                        $time = new \Nette\Utils\DateTime($this->session->getSection($col)->value);
                        
                    $where[] = [
                        'column' => $columnName,
                        'type'   => $val['operator'],
                        'value'  => $time->getTimestamp(),
                        'strint' => $val['strint']
                    ];
                    } else {
                        $where[] = [
                        'column' => $columnName,
                        'type'   => $val['operator'],
                        'value'  => $this->session->getSection($col)->value,
                        'strint' => $val['strint']
                    ];
                    } 
                    
                }
            }

            \Tracy\Debugger::barDump($where);
            
            return $where;
        }
    }

    /**
     * adds filter
     *
     * @param string $columnName
     * @param string $text
     * @param string $type
     * @param array  $data
     */
    public function addFilter($columnName, $text, $type, array $data = [])
    {
        switch ($type) {
            case self::TEXT_EQUAL:
                $this->form->addText($columnName, $text);
                $this->type[$columnName] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '=',
                    'strint'   => '%s'
                ];
                break;
            case self::TEXT_LIKE:
                $this->form->addText($columnName, $text);
                $this->type[$columnName] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => 'LIKE',
                    'strint'   => '%~like~'
                ];
                break;
            case self::INT_EQUAL:
                $this->form->addText($columnName, $text)
                    ->setRequired(false)
                    ->addRule(Form::INTEGER);
                $this->type[$columnName] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '=',
                    'strint'   => '%i'
                ];
                break;
            case self::NOTHING:
                $this->type[self::NOTHING] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => null,
                    'strint'   => 'null'
                ];
                break;
            case self::FROM_TO_INT:
                $this->form->addText($columnName . '_Xfrom', $text)
                    ->setRequired(false)
                    ->addRule(Form::INTEGER)
                    ->setAttribute('placeholder', 'From')
                    ->setAttribute('class', 'mb-1');
                
                $this->form->addText($columnName . '_Xto', $text)
                    ->setRequired(false)
                    ->addRule(Form::INTEGER)
                    ->setAttribute('placeholder', 'To');
                $this->type[$columnName . '_Xfrom'] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '>=',
                    'strint'   => '%i'
                ];
                $this->type[$columnName . '_Xto']   = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '<=',
                    'strint'   => '%i'
                ];
                break;
            case self::CHECKBOX_LIST:
                $this->form->addCheckboxList($columnName, $text, $data)->setTranslator(null);
                
                $this->type[$columnName] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => 'IN',
                    'strint'   => '%in'
                ];
                break;
            case self::DATE_TIME:
                $this->form->addTbDatePicker($columnName. '_Xfrom', $text);
                $this->form->addTbDatePicker($columnName. '_Xto', $text);
                                $this->type[$columnName. '_Xfrom'] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '>=',
                    'strint'   => '%i'
                ];                
                $this->type[$columnName. '_Xto'] = [
                    'type'     => $type,
                    'text'     => $text,
                    'operator' => '<=',
                    'strint'   => '%i'
                ];  
                
                break;
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function checkFTI($name)
    {
        $columnName = $name;

        if (preg_match('#_Xfrom$#', $name)) {
            $columnName = str_replace('_Xfrom', '', $name);
        }

        if (preg_match('#_Xto$#', $name)) {
            $columnName = str_replace('_Xto', '', $name);
        }

        return $columnName;
    }
    
    public function handleReset()
    {        
        foreach ( $this->type as $name => $type) {
            unset($this->session->getSection($name)->value);
        }
        
        $this->whereColumns = [];
    }

    /**
     * renders grid filter
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'gridFilter' . $sep . 'gridFilter.latte');
        $template->setTranslator($this->translator);

        foreach ($this->type as $column => $value) {            
            $this['gridFilter']->setDefaults([$column => $this->session->getSection($column)->value]);
        }

        $template->type = $this->type;
        $template->gf   = $this;

        // TODO params is var of template class
        $template->params = $this->getParameters();

        $template->render();
    }
    
    /**
     * renders active filters
     */
    public function renderActiveFilters()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'gridFilter' . $sep . 'activeFilters.latte');
        $template->setTranslator($this->translator);
        
        $template->filters = $this->getWhere();
        
        $template->render();
    }
    
    public function renderReset()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'gridFilter' . $sep . 'reset.latte');        
        $this->template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGridFilter()
    {
        $this->form->setAction(
            $this->link('this', $this->getParameters())
        );
        $this->form->onSuccess[] = [$this, 'success'];
        $this->form->addSubmit('send', 'Send');
        $this->form->setTranslator($this->translator);

        return $this->form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function success(Form $form, ArrayHash $values)
    {
        $where = [];

        foreach ($this->type as $name => $type) {
            if (isset($values[$name]) && $name !== self::NOTHING) {
                $section        = $this->session->getSection($name);
                $section->value = $values[$name];

                if ($type['operator'] === 'IN') {
                    $where[] = [
                        'column' => $name,
                        'type'   => $type['operator'],
                        'value'  => "'" . implode(', ', $values[$name]) . "'",
                    ];
                } else {
                    $where[] = [
                        'column' => $name,
                        'type'   => $type['operator'],
                        'value'  => "'" . $values[$name] . "'",
                    ];
                }
            }
        }
        
        $this->whereColumns = $where;

        $this->redirect('this');
    }    
}
