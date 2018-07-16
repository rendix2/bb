<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\RanksManager;
use App\Settings\Ranks;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of RankPresenter
 *
 * @author rendi
 * @method RanksManager getManager()
 */
class RankPresenter extends Base\AdminPresenter
{
    /**
     * @var int
     */
    const N = -1;
    
    /**
     * @var Ranks $ranks
     * @inject
     */
    public $ranks;

    /**
     * RankPresenter constructor.
     *
     * @param RanksManager $manager
     */
    public function __construct(RanksManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * startup method
     */
    public function startup()
    {
        parent::startup();
        
        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('rank_id', 'rank_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('rank_name', 'rank_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
    }
    
    /**
     * 
     * @param int $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);
        
        $this->template->ranksDir = $this->ranks->getTemplateDir();
    }

        /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        
        $form->addText('rank_name', 'Rank name:')->setRequired(true);
        $form->addInteger('rank_from', 'Rank from:');
        $form->addInteger('rank_to', 'Rank to:');
        $form->addUpload('rank_file', 'Rank file:');
        $form->addCheckbox('rank_special', 'Rank special:');

        $form->onValidate[] = [$this, 'onValidate'];

        return $this->addSubmitB($form);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function onValidate(Form $form, ArrayHash $values)
    {
        if ($values->rank_special) {
            if ($values->rank_to || $values->rank_from) {
                $form->addError('Special rank have not Rank from and Rank to.');
            }
        } else {
            if (!is_numeric($values->rank_from)) {
                $form->addError('Rank from is not numeric.');
            }

            if (!is_numeric($values->rank_to)) {
                $form->addError('Rank to is not numeric.');
            }

            if ($values->rank_from === $values->rank_to) {
                $form->addError('From and to should not to be same.');
            }
        }
    }    

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        $move = $this->getManager()
            ->moveRank(
                $values->rank_file,
                $this->getParameter('id')
            );

        if ($move !== RanksManager::NOT_UPLOADED) {
            $values->rank_file = $move;
        } else {
            unset($values->rank_file);
        }

        parent::editFormSuccess($form, $values);
    }
}
