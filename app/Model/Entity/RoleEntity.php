<?php declare(strict_types=1);

namespace App\Model\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'role', options: ['comment' => 'Roles'])]
class RoleEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: Types::STRING, length: 512, options: ['comment' => 'Name'])]
    public string $name;

    #[Column(type: Types::TEXT, options: ['comment' => 'Name'])]
    public string $description;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, UserEntity> $users
     */
    // cascade: 'remove' on a ManyToMany cascades an actual ENTITY remove, not just a join-table row delete - it would make
    // deleting one role delete every user who has it (same fix as web-project); the join row goes away on its own
    #[ManyToMany(targetEntity: UserEntity::class, inversedBy: 'roles', cascade: ['persist'])]
    #[JoinTable(
        name: 'user_role',
        joinColumns: [
            new JoinColumn('role_id', 'id', onDelete: 'CASCADE', options: ['comment' => 'Role ID']),
        ],
        inverseJoinColumns: [
            new JoinColumn('user_id', 'id', onDelete: 'CASCADE', options: ['comment' => 'User ID']),
        ],
        options: ['comment' => 'User roles'],
    )]
    public Collection $users;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->users = new ArrayCollection();
    }

}
