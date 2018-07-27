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

    /**
     * BBIdentity constructor.
     *
     * @param \App\Models\UsersManager $userManager
     * @param                          $id
     * @param array                    $roles
     * @param array                    $data
     */
    public function __construct(\App\Models\UsersManager $userManager, $id, array $roles = [], array $data = [])
    {
        $this->userManager = $userManager;
        $this->id          = $id;
        $this->roles       = $roles;
    }

    /**
     * @return int|mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getData()
    {              
        return [];//array_merge($this->userManager->getById($this->id)->toArray(), $this->data);
    }
}
