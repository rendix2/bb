<?php

namespace App\Models\Traits;

use App\Models\CategoriesManager;

/**
 * Description of CategoriesTrait
 *
 * @author rendix2
 */
trait CategoriesTrait {

    /**
     * @var CategoriesManager $categoriesManager
     * @inject
     */
    public $categoriesManager;
    
    /**
     * 
     * @param int $category_id
     * 
     * @return \App\Models\Entity\Category
     */
    public function checkCategoryParam($category_id)
    {
        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $categoryDibi = $this->categoriesManager->getById($category_id);

        if (!$categoryDibi) {
            $this->error('Category was not found.');
        }
        
        $category = \App\Models\Entity\Category::setFromRow($categoryDibi);

        if (!$category->getCategory_active()) {
            $this->error('Category is not active.');
        }

        return $category;
    }
}
