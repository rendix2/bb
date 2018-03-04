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
    const FROM_TO_INT = 'FTI';

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
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => '=', 'strint' => '%s'];
                break;
            case self::TEXT_LIKE:
                $this->form->addText($columnName, $text);
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => 'LIKE', 'strint' => '%s'];
                break;
            case self::INT_EQUAL:
                $this->form->addText($columnName, $text)->setRequired(false)->addRule(\Nette\Application\UI\Form::INTEGER);
                $this->type[$columnName] = ['type' => $type, 'text' => $text, 'operator' => '=', 'strint' => '%i'];
                break;
            case self::NOTHING:
                $this->type[self::NOTHING] = ['type' => $type, 'text' => $text, 'operator' => null, 'strint' => 'null'];
                break;
            case self::FROM_TO_INT:
                $this->form->addText($columnName . '_Xfrom', $text)->setRequired(false)->addRule(\Nette\Application\UI\Form::INTEGER)->setAttribute('placeholder', 'From')->setAttribute('class', 'mb-1');
                $this->form->addText($columnName . '_Xto', $text)->setRequired(false)->addRule(\Nette\Application\UI\Form::INTEGER)->setAttribute('placeholder', 'To');
                $this->type[$columnName . '_Xfrom'] = ['type' => $type, 'text' => $text, 'operator' => '>=', 'strint' => '%i'];
                $this->type[$columnName . '_Xto'] = ['type' => $type, 'text' => $text, 'operator' => '<=', 'strint' => '%i'];
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
            return $this->whereColumns;
        } else {
            // get from session
            $where = [];

            if (is_array($this->type)) {
                foreach ($this->type as $col => $val) {
                    if (( $val['strint'] == '%i' && is_numeric($this->session->getSection($col)->value) ) || ($val['strint'] == '%s' && is_string($this->session->getSection($col)->value) && mb_strlen($this->session->getSection($col)->value) >= 1 ) && $col !== self::NOTHING) {
                        $columnName = $col;

                        if (preg_match('#_Xfrom$#', $col)) {
                            $columnName = str_replace('_Xfrom', '', $col);
                        }

                        if (preg_match('#_Xto$#', $col)) {
                            $columnName = str_replace('_Xto', '', $col);
                        }

                        if ($val['operator'] == 'LIKE') {
                            $where[] = ['column' => $columnName, 'type' => $val['operator'], 'value' => '%' . $this->session->getSection($col)->value . '%', 'strint' => $val['strint']];
                        } else {
                            $where[] = ['column' => $columnName, 'type' => $val['operator'], 'value' => $this->session->getSection($col)->value, 'strint' => $val['strint']];
                        }
                    }
                }
            }
            return $where;
        }
    }

    public function getOrderBy() {
        $orderBy = [];
        $params = $this->getParameters() ? $this->getParameters() : $this->orderBy;

        foreach ($this->getParameters() as $param => $value) {
            $param = str_replace('sort_', '', $param);
            $orderBy[$param] = ($value === 'ASC' || $value === 'DESC') ? $value : null;
        }

        return $orderBy;
    }

    public function success(\Nette\Forms\Form $form, \Nette\Utils\ArrayHash $values) {
        $where = [];

        foreach ($this->type as $name => $type) {
            if ((isset($values[$name]) ) && $name != self::NOTHING) {
                $section = $this->session->getSection($name);
                $section->value = $values[$name];

                $where[] = ['column' => $name, 'type' => $type['operator'], 'value' => "'" . $values[$name] . "'",];
            }
        }

        $this->whereColumns = $where;

        $this->redirect('this');
    }

    public function render() {
        $template = $this->template->setFile(__DIR__ . '/templates/gridFilter.latte');

        foreach ($this->type as $column => $value) {
            $this['gridFilter']->setDefaults([$column => $this->session->getSection($column)->value]);
        }

        $template->type = $this->type;
        $template->gf = $this;
        $template->params = $this->getParameters();

        $template->render();
    }

}
