<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\RanksManager;

/**
 * Description of RankPresenter
 *
 * @author rendi
 */
class RankPresenter extends Base\AdminPresenter
{

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
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        return $this->addSubmitB($form);
    }

}
