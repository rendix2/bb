<?php

namespace App\Controls;

/**
 * Description of GridFilter
 *
 * @author rendi
 */
class GridFilter extends \Nette\Application\UI\Control {

    const TEXT_EQUAL = 'teq';
    const TEXT_LIKE = 'tl';
    const INT_EQUAL = 'i';
    const NOTHING = 'empty';

    private $whereColumns;
    private $limit = 100;
    private $form;
    private $type;
    private $orderBy;
    private $session;

    public function __construct() {
        parent::__construct();

        $this->form = new BootstrapForm();
    }

    public function factory(\Nette\Http\Session $session) {
        $this->session = $session;
    }

    public function addFilter($columnName, $text, $type) {
        switch ($type) {
            case self::TEXT_EQUAL:
                $this->form->addText($columnName, $text);
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => '=', 'before' => "'", 'after' => "'"];
                break;
            case self::TEXT_LIKE:
                $this->form->addText($columnName, $text);
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => 'LIKE', 'before' => "'%", 'after' => "%'"];
                break;            
            case self::INT_EQUAL:
                $this->form->addInteger($columnName, $text);
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => '=','before' => "", 'after' => ""];
                break;
            case self::NOTHING:
                $this->type[$columnName] = ['type' => $type, 'text' => $text];
                break;
        }
    }   

    protected function createComponentGridFilter() {
        $this->form->setAction($this->link('this', $this->getParameters()));
        $this->form->onSuccess[] = [$this, 'success'];
        $this->form->addSubmit('send', 'SEND');

        return $this->form;
    }

    public function getWhere() {

        if ($this->whereColumns) {
            // get from form
            return $this->whereColumns;
        } else {
            // get from session
            $where = [];
            
            if ( is_array($this->type) ){
            foreach ($this->type as $col => $val) {
                if ($this->session->getSection($col)->value){
                    $where[] = ['column' => $col, 'type' => $val['operator'], 'value' => $this->session->getSection($col)->value, 'before' => $val['before'], 'after' => $val['after']];
                }
            }
            }

            return $where;
        }
    }

    public function getOrderBy() {
        $orderBy = [];

        $params =  $this->getParameters() ? $this->getParameters() : $this->orderBy;
        foreach ( $this->getParameters() as $param => $value) {
            $param = str_replace('sort_', '', $param);
            $orderBy[$param] = ($value === 'ASC' || $value === 'DESC') ? $value : null;
        }
        
        return $orderBy;
    }

    public function success(\Nette\Forms\Form $form, \Nette\Utils\ArrayHash $values) {
        $where = [];

        foreach ($this->type as $name => $type) {
            $section = $this->session->getSection($name);
            $section->value = $values[$name];

            $where[] = ['column' => $name, 'type' => $type['operator'], 'value' => "'".$values[$name]."'",];
        }

        $this->whereColumns = $where;
        
        $this->redirect('this');
    }

    public function render() {
        $template = $this->template->setFile(__DIR__ . '/templates/gridFilter.latte');

        foreach ($this->type as $column => $value) {
            $this['gridFilter']->setDefaults([$column => $this->session->getSection($column)->value]);
        }

        $template->type   = $this->type;
        $template->gf     = $this;
        $template->params = $this->getParameters();

        $template->render();
    }

}
