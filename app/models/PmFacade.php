<?php

namespace App\Models;

/**
 * Description of PmFacade
 *
 * @author rendi
 */
class PmFacade 
{    
    /**
     *
     * @var PmManager $pmManager
     */
    private $pmManager;

    /**
     * 
     * @param \App\Models\PmManager $pmManager
     */
    public function __construct(PmManager $pmManager) 
    {
        $this->pmManager = $pmManager;
    }
    
    public function delete($user_id)
    {
        $this->pmManager->deleteByUserFrom($user_id);
        $this->pmManager->deleteByUserTo($user_id);
    }
}
