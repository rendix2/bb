<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Models\PostFilesManager;
use Nette\Application\UI\Form;
use App\Controls\GridFilter;

/**
 * Description of Files
 *
 * @author rendi
 */
class FilePresenter extends AdminPresenter
{
    /**
     * FilePresenter constructor.
     *
     * @param PostFilesManager $manager
     */
    public function __construct(PostFilesManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return Form
     */
    protected function createComponentEditForm()
    {
        return null;
    }

    /**
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;        
    }

}
