<?php

namespace App\Models;

/**
 * Description of CategoryFacade
 *
 * @author rendi
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
     * @param \App\Models\CategoriesManager $categoriesManager
     * @param \App\Models\ForumFacade $forumFacade
     * @param \App\Models\ForumsManager $forumsManager
     */
    public function __construct(CategoriesManager $categoriesManager, ForumFacade $forumFacade, ForumsManager $forumsManager)
    {
        $this->categoriesManager = $categoriesManager;
        $this->forumFacade       = $forumFacade;
        $this->forumsManager     = $forumsManager;
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
                
        $forums = $this->forumsManager->getByCategory($item_id)->fetchAll();
        
        foreach ($forums as $forum) {
            $this->forumFacade->delete($forum->forum_id);
        }
        
        return $this->categoriesManager->delete($item_id);
    }    
}
