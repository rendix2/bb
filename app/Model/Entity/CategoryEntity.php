<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'category')]
class CategoryEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'children')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $parent;

    #[Column(type: Types::TEXT)]
    public string $name;

    #[Column(type: Types::INTEGER)]
    public int $order;

    #[Column(type: Types::BOOLEAN)]
    public int $active;




    /**
     * @var Collection<int, CategoryEntity> $children
     */
    #[OneToMany(targetEntity: CategoryEntity::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    public Collection $children;

}