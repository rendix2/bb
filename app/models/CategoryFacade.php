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
     * @param ArrayHash $item_data
     */
    public function add(ArrayHash $item_data)
    {
        $category_id = $this->categoriesManager->getMptt()
            ->add($item_data->category_parent_id, $item_data->category_name);
        
        $this->categoriesManager->update($category_id, $item_data);
        
        return $category_id;
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
        $subForums = $this->forumsManager->getByParent($item_id);
        
        foreach ($subForums as $subForum) {
            $this->delete($subForum->forum_id);
        }
                
        $forums = $this->forumsManager->getFluentByCategory($item_id)->fetchAll();
        
        foreach ($forums as $forum) {
            $this->forumFacade->delete($forum->forum_id);
        }
        
        return $this->categoriesManager->delete($item_id);
    }
}
