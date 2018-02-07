<?php

namespace App\ForumModule\Presenters;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 * @method \App\Models\ForumsManager getManager()
 */
final class ForumPresenter extends Base\ForumPresenter {
    
    private $categoryManager;
    
    /**
     * 
     * @param \App\Models\ForumsManager $manager
     */
    public function __construct(\App\Models\ForumsManager $manager) {
        parent::__construct($manager);
    }
    
    public function injectCategoryManager(\App\Models\CategoriesManager $categoryManager){
        $this->categoryManager = $categoryManager;
    }

    public function renderDefault($forum_id, $page = 1){          
        $forum = $this->getManager()->getById($forum_id);
        
        if ( !$forum ){
            $this->error('Forum not exists.');
        }
        
        if ( !$forum->forum_active ){
          $this->error('Forum is not active.');   
        }
        
        $category = $this->categoryManager->getByForumId($forum_id);
        
        if ( !$category ){
            $this->error('Not existing category.');
        }
        
        if ( !$category->category_active ){
            $this->error('Category is not active.');
        }
        
        $topics    = $this->getManager()->getTopics($forum_id);               
        $paginator = new \App\Controls\PaginatorControl($topics, 10, 5, $page);
        
        $this->addComponent($paginator, 'paginator');
        
        if (!$paginator->getCount()){
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }
               
        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()->getForumsByForumParentId($forum_id);  
        $this->template->parentForum = $this->getManager()->getParentForumByForumId($forum_id);                
    }
}
