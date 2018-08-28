<?php

namespace App\Authorization;

use App\Models\UsersManager;

/**
 * Description of BBIdentity
 *
 * @author rendix2
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
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * BBIdentity constructor.
     *
     * @param UsersManager $userManager
     * @param int          $id
     * @param array        $roles
     * @param array        $data
     */
    public function __construct(UsersManager $userManager, $id, array $roles = [], array $data = [])
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
