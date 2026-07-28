<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Models\FaqManager;
use Contributte\Datagrid\Datagrid;
use Contributte\FormsBootstrap\BootstrapForm;

/**
 * Description of FaqPresenter
 *
 * @author rendix2
 * @method FaqManager getManager()
 * @package App\AdminModule\Presenters
 */
class FaqPresenter extends AdminPresenter
{
    /**
     * FaqPresenter constructor.
     *
     * @param FaqManager $manager
     */
    public function __construct(FaqManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm(): BootstrapForm
    {
        $form = new BootstrapForm();

        return $form;
    }


    protected function createComponentDataGrid(): Datagrid
    {
        $dataGrid = new Datagrid();

        return $dataGrid;
    }

}
