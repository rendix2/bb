<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of ThanksFacade
 *
 * @author rendi
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
     * @param \App\Models\ThanksManager $thanksManager
     * @param \App\Models\UsersManager $usersManager
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
     * @return type
     */
    public function add(ArrayHash $item_data)
    {
        $this->usersManager->update($item_data->thank_user_id, ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1']));
        
        return $this->thanksManager->add($item_data);
    }    
}
