<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of CategoryFacade
 *
 * @author rendix2
 */
class CategoryFacade
{
    /**
     * @var CategoriesManager $categoriesManager
     */
    private $categoriesManager;
    
    /**
     *
     * @var ForumFacade $forumFacade
     */
    private $forumFacade;
    
    /**
     *
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;

    /**
     *
     * @param CategoriesManager $categoriesManager
     * @param ForumFacade       $forumFacade
     * @param ForumsManager     $forumsManager
     */
    public function __construct(
        CategoriesManager $categoriesManager,
        ForumFacade $forumFacade,
        ForumsManager $forumsManager
    ) {
        $this->categoriesManager = $categoriesManager;
        $this->forumFacade       = $forumFacade;
        $this->forumsManager     = $forumsManager;
    }
    
    /**
     *
     * @param Entity\Category $category
     */
    public function add(Entity\Category $category)
    {
        $category->category_id = $this->categoriesManager->getMptt()
            ->add($category->category_parent_id, $category->category_name);
        
        $this->categoriesManager->update($category->category_id, $category->getArrayHash());
        
        return $category->category_id;
    }
    
    /**
     * 
     * @param type $item_id
     * @param ArrayHash $item_data
     * 
     * @return type
     */
    public function update($item_id, ArrayHash $item_data)
    {
        $category = $this->categoriesManager->getById($item_id);
        
        if ($category->category_parent_id !== $item_data->category_parent_id) {
            $this->categoriesManager->getMptt()->move($item_id, $item_data->category_parent_id);
            
            unset($item_data->category_parent_id);
        }
        
        return $this->categoriesManager->update($item_id, $item_data);
    }

    /**
     *
     * @param int $item_id
     *
     * @return bool
     */
    public function delete($item_id)
    {
        $subCategories = $this->categoriesManager->getByParent($item_id);
        
        foreach ($subCategories as $subCategory) {
            $this->delete($subCategory->category_id);
        }
                
        $forums = $this->forumsManager->getFluentByCategory($item_id)->fetchAll();
        
        foreach ($forums as $forum) {
            $this->forumFacade->delete($forum->forum_id);
        }
        
        return $this->categoriesManager->delete($item_id);
    }
}
