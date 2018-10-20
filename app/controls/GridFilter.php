<?php

namespace App\Controls;

use Dibi\Fluent;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of GridFilter
 *
 * @author rendix2
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
    const TEXT_FULTEXT = 'tf';
    
    /**
     * @var string
     */
    const INT_EQUAL = 'ieq';
    
    /**
     * @var string
     */
    const INT_LIKE = 'il';

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
     * @var string
     */    
    const SESSION_PREFIX = 'grid_filter_';

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
    private $filters;

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
    public function __construct(Session $session)
    {
        parent::__construct();

        $this->form    = BootstrapForm::create();
        $this->filters = [];
        $this->session = $session;
    }

    /**
     * @param ITranslator $translator
     */
    public function setTranslator(ITranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function applyOrderBy(Fluent $fluent)
    {
        foreach ($this->getParameters() as $columnName => $sortType) {
            if (!$sortType || !strpos('sort_', $columnName)) {
                continue;
            }
            
            $columnName = $this->checkFTI(str_replace('sort_', '', $columnName));

            $fluent->orderBy($columnName, $sortType);
        }
    }
    
    public function getColumnsSearchedBy()
    {
        $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;        
        $where       = [];        
        
        foreach ($this->filters as $columnName => $val) {   
            $value = $this->session->getSection($sessionName)->{$columnName};
            
            if ((is_string($value) && mb_strlen($value)) || (is_array($value) && count($value)) || $value ) {
                $where[$columnName] = $val['text'];
            }
        }
        
        return $where;
    }

    /**
     * @return array
     */
    public function applyWhere(Fluent $fluent)
    {
        if ($this->parent !== null) {
            $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;
        }

        foreach ($this->filters as $col => $val) {
            $value = $this->session->getSection($sessionName)->{$col};
                
            if ((is_string($value) && !mb_strlen($value)) || (is_array($value) && !count($value)) || $value === null) {
                continue;
            }
                
            $columnName = $this->checkFTI($col);
                               
            switch($val['type']) {
                case self::TEXT_FULTEXT:
                    $fluent->where('MATCH(%n) AGAINST (%s IN BOOLEAN MODE)', $columnName, $value);
                    break;
                case self::TEXT_EQUAL:
                    $fluent->where('%n = %s', $columnName, $value);
                    break;
                case self::INT_EQUAL:
                    $fluent->where('%n = %i ', $columnName, $value);
                    break;
                case self::INT_LIKE:
                case self::TEXT_LIKE:
                    $fluent->where('%n LIKE %~like~', $columnName, $value);                
                    break;
                case self::FROM_TO_INT:
                    if (strpos($col, '_Xfrom')) {
                        $fluent->where('%n >= %i', $columnName, $value);
                    } else if (strpos($col, '_Xto')) {
                        $fluent->where('%n <= %i', $columnName, $value);
                    }
                    break;
                case self::DATE_TIME:
                    $time = new DateTime($value);
                        
                    if (strpos($col, '_Xfrom')) {
                        $fluent->where('%n >= %i', $columnName, $time->getTimestamp());
                    } else if (strpos($col, '_Xto')) {
                        $fluent->where('%n <= %i', $columnName, $time->getTimestamp());
                    }
                    break;
                case self::CHECKBOX_LIST:
                    $fluent->where('%n IN %in', $columnName, $value);
                    break;
            }
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
            case self::TEXT_LIKE:
            case self::TEXT_FULTEXT:
                $this->form->addText($columnName, $text);
                break;            
            case self::INT_EQUAL:
            case self::INT_LIKE:
                $this->form->addText($columnName, $text)
                    ->setRequired(false)
                    ->addRule(Form::INTEGER);
                break;           
            case self::NOTHING:
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
                break;

            case self::DATE_TIME:
                $this->form->addTbDatePicker($columnName. '_Xfrom', $text)
                    ->setAttribute('placeholder', 'From')
                    ->setAttribute('class', 'mb-1');
                $this->form->addTbDatePicker($columnName. '_Xto', $text)
                        ->setAttribute('placeholder', 'To');             
                break;    
            
            case self::CHECKBOX_LIST:
                $this->form->addCheckboxList($columnName, $text, $data);
                break;
            default :
                throw new \InvalidArgumentException('Unknow filter type.');
        
        }
        
        if ($type === self::DATE_TIME || $type === self::FROM_TO_INT) {
                $this->filters[$columnName. '_Xfrom'] = [
                    'type'     => $type,
                    'text'     => $text,
                ];
                
                $this->filters[$columnName. '_Xto'] = [
                    'type'     => $type,
                    'text'     => $text,
                ];             
        } else {
           $this->filters[$columnName] = [
                    'type'     => $type,
                    'text'     => $text,
                ]; 
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
        $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;
        
        foreach ($this->filters as $name => $type) {
            unset($this->session->getSection($sessionName)->{$name});
        }

    }
    
    /**
     * 
     * @param string $column
     */
    public function handleStorno($column)
    {
        $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;
        
        unset($this->session->getSection($sessionName)->{$column});
    }

    /**
     * renders grid filter
     */
    public function render()
    {
        if (!$this->translator) {
            throw new Exception('Translator was not set.');
        }
        
        $sep         = DIRECTORY_SEPARATOR;
        $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;               
        $template    = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'gridFilter' . $sep . 'gridFilter.latte');
        
        
        $template->setTranslator($this->translator);

        foreach ($this->filters as $column => $value) {
            if ($this->session->getSection($sessionName)) {
                $this['gridFilter']->setDefaults([$column => $this->session->getSection($sessionName)->{$column}]);
            }
        }

        $template->filters    = $this->filters;
        $template->type_empty = self::NOTHING;
        $template->type_fti   = self::FROM_TO_INT;
        $template->type_date  = self::DATE_TIME;

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
        
        $template->filters = $this->getColumnsSearchedBy();
        
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
        $sessionName = self::SESSION_PREFIX . $this->presenter->name. ':' . $this->presenter->action;

        foreach ($this->filters as $name => $type) {
            if (isset($values[$name]) && $name !== self::NOTHING) {
                $section        = $this->session->getSection($sessionName);
                $section[$name] = $values[$name];
            }
        }

        $this->redirect('this');
    }
}
