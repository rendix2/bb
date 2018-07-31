<?php

namespace App\AdminModule\Presenters;

use App\Controls\GridFilter;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;

/**
 * Description of CategoryPresenter
 *
 * @author rendi
 */
class CategoryPresenter extends Base\AdminPresenter
{
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;
    
    /**
     * CategoryPresenter constructor.
     *
     * @param CategoriesManager $manager
     * @param GridFilter        $gf
     */
    public function __construct(CategoriesManager $manager, GridFilter $gf)
    {
        parent::__construct($manager);
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this[self::FORM_NAME]->setDefaults($item);

            $forums = $this->forumsManager->createForums(
                $this->forumsManager->getByCategory($id)->fetchAll(),
                0
            );

            if (!$forums) {
                $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_WARNING);
            }

            $this->template->item   = $item;
            $this->template->title  = $this->getTitleOnEdit();
            $this->template->forums = $forums;
        } else {
            $this->template->title  = $this->getTitleOnAdd();
            $this->template->forums = [];

            $this[self::FORM_NAME]->setDefaults([]);
        }
    }
    
    public function handleReorder()
    {
        // todo
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('category_name', 'Category name:')->setRequired(true);
        $form->addCheckbox('category_active', 'Category active:');

        return $this->addSubmitB($form);
    }
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getAdminTranslator());
            
        $this->gf->addFilter('category_id', 'category_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('category_name', 'category_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(null, null, GridFilter::NOTHING);

        return $this->gf;
    }
}
