<?php

namespace App\Models\Traits;

use App\Models\CategoryManager;
use App\Models\Entity\CategoryEntity;
use Nette\DI\Attributes\Inject;

/**
 * Description of CategoriesTrait
 *
 * @author rendix2
 */
trait CategoriesTrait
{

    /**
     * @var CategoryManager $categoryManager
     * @inject
     */
    #[Inject]
    public CategoryManager $categoryManager;
    
    /**
     *
     * @param int $category_id
     *
     * @return CategoryEntity
     */
    public function checkCategoryParam(int $category_id)
    {
        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $categoryDibi = $this->categoryManager->getById($category_id);

        if (!$categoryDibi) {
            $this->error('Category was not found.');
        }
        
        $category = CategoryEntity::setFromRow($categoryDibi);

        if (!$category->getCategory_active()) {
            $this->error('Category is not active.');
        }

        return $category;
    }
}
