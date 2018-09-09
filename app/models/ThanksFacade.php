<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksFacade
 *
 * @author rendix2
 */
class ThanksFacade
{
    /**
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;
    
    /**
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * @param ThanksManager $thanksManager
     * @param UsersManager $usersManager
     */
    public function __construct(ThanksManager $thanksManager, UsersManager $usersManager)
    {
        $this->thanksManager = $thanksManager;
        $this->usersManager  = $usersManager;
    }

    /**
     *
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        $this->usersManager->update(
            $item_data->thank_user_id,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1'])
        );

        return $this->thanksManager->add($item_data);
    }
    
    /**
     * 
     * @param int $category_id
     */
    public function deleteByCategory($category_id)
    {
        
    }

    /**
     * 
     * @param int $forum_id
     */
    public function deleteByForum($forum_id)
    {
        
    }

    /**
     * 
     * @param int $topic_id
     */
    public function deleteByTopic($topic_id)
    {
        
    }
    
    /**
     * 
     * @param int $post_id
     */
    public function deleteByPost($post_id)
    {
        
    }

    /**
     * 
     * @param int $user_id
     */
    public function deleteByUser($user_id)
    {
        
    }
}
