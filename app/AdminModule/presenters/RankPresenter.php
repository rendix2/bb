<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\RanksManager;

/**
 * Description of RankPresenter
 *
 * @author rendi
 * @method RanksManager getManager()
 */
class RankPresenter extends Base\AdminPresenter {

    const N = -1;
    
    private $wwwDir;


    /**
     * RankPresenter constructor.
     *
     * @param RanksManager $manager
     */
    public function __construct(RanksManager $manager) {
        parent::__construct($manager);
    }
    
    public function injectWwwDir(\App\Controls\WwwDir $wwwDir){
        $this->wwwDir = $wwwDir;
    }
    

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addText('rank_name', 'Rank name:')->setRequired(true);
        $form->addInteger('rank_from', 'Rank from:');
        $form->addInteger('rank_to', 'Rank to:');
        $form->addUpload('rank_file', 'Rank file:');

        $checkbox = $form->addCheckbox('rank_special', 'Rank special:');
        /*
          $checkbox->addConditionOn($form['rank_from'], \Nette\Application\UI\Form::FILLED)
          ->addRule(\Nette\Application\UI\Form::BLANK, 'Post from have to be empty', $form['rank_from']);
          $checkbox->addConditionOn($form['rank_to'], \Nette\Application\UI\Form::FILLED)
          ->addRule(\Nette\Application\UI\Form::BLANK, 'Post to have to be empty', $form['rank_to']);
         * 
         */

        $form->onValidate[] = [$this, 'onValidate'];

        return $this->addSubmitB($form);
    }

    public function onValidate(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        if ($values->rank_special) {
            if ($values->rank_to || $values->rank_from) {
                $form->addError('Special rank have not Rank from and Rank to');
            }
        } else {
            if (!is_numeric($values->rank_from)) {
                $form->addError('Rank from is not numeric');
            }

            if (!is_numeric($values->rank_to)) {
                $form->addError('Rank to is not numeric');
            }
            
            if ( $values->rank_from === $values->rank_to ){
                $form->addError('From and to should not to be same.');
            }
        }
    }

    public function editFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $move = $this->getManager()->moveRank($values->rank_file, $this->getParameter('id'), $this->wwwDir->wwwDir);

        if ($move !== RanksManager::NOT_UPLOADED) {
            $values->rank_file = $move;
        } else {
            unset($values->rank_file);
        }

        parent::editFormSuccess($form, $values);
    }

}
