<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\GridFilter;
use App\Models\FilesManager;
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
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);

        return $this->gf;        
    }

}
