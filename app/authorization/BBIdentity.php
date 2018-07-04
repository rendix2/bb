<?php

namespace App\Authorization;

/**
 * Description of BBIdentity
 *
 * @author rendi
 */
class BBIdentity implements \Nette\Security\IIdentity
{
    /**
     *
     * @var int $id
     */
    private $id;
    
    /**
     *
     * @var array $roles
     */
    private $roles;
    
    /**
     *
     * @var array $data
     */
    private $data;
    
    /**
     * @var \App\Models\UsersManager $userManager
     */
    private $userManager;


    public function __construct(\App\Models\UsersManager $userManager, $id, array $roles = [], array $data = [])
    {
        $this->userManager = $userManager;
        $this->id          = $id;
        $this->roles       = $roles;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }
    
    public function getData()
    {              
        return [];//array_merge($this->userManager->getById($this->id)->toArray(), $this->data);
    }
}
