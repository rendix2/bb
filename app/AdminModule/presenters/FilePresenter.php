<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Models\FilesManager;
use Contributte\Datagrid\Datagrid;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Form;

/**
 * Description of Files
 *
 * @author rendix2
 * @method FilesManager getManager()
 * @package App\AdminModule\Presenters
 */
class FilePresenter extends AdminPresenter
{
    /**
     * FilePresenter constructor.
     *
     * @param FilesManager $manager
     */
    public function __construct(FilesManager $manager)
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
