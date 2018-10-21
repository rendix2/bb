<?php

namespace App\ForumModule\Presenters;

use App\Models\CategoriesManager;
use App\Controls\BreadCrumbControl;
use dibi;

/**
 * Description of CategoryPresenter
 *
 * @author rendix2
 * @method CategoriesManager getManager()
 */
class CategoryPresenter extends Base\ForumPresenter
{
    /**
     * 
     * @param CategoriesManager $manager
     */
    public function __construct(CategoriesManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * 
     * @param int $category_id
     */
    public function renderDefault($category_id = 0)
    {
        $category   = $this->checkCategoryParam($category_id);        
        $categories = $this->getManager()->getMptt()->get_tree($category_id);
        $forums = $this->forumsManager
                ->getFluentByCategory($category_id)
                ->orderBy('forum_left', dibi::ASC)                
                ->fetchAll();

        if ($forums) {
            $this->template->forums = $forums;
        } else {
            $this->template->forums = [];
            
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }
        
        if ($categories) {
            $this->template->categories = $categories;
        } else {            
            $this->template->categories = [];
            
            $this->flashMessage('No subcategories.', self::FLASH_MESSAGE_DANGER);
        }
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbCategory()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_category']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }    
}
