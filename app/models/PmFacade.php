<?php

namespace App\Models;

/**
 * Description of PmFacade
 *
 * @author rendix2
 * @package App\Models
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
     * @param PmManager $pmManager
     */
    public function __construct(PmManager $pmManager) 
    {
        $this->pmManager = $pmManager;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->pmManager = null;
    }

    /**
     * 
     * @param int $user_id
     */
    public function delete($user_id)
    {
        $this->pmManager->deleteByUserFrom($user_id);
        $this->pmManager->deleteByUserTo($user_id);
    }
}
