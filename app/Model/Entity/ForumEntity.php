<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\ForumRepository;
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
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: ForumRepository::class)]
#[Table(name: 'forum')]
class ForumEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[Column(type: Types::TEXT)]
    public string $name;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'forums')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'forums')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $parent;

    #[GeneratedValue()]
    #[Column(type: Types::BOOLEAN)]
    public bool $active;

    /**
     * @var Collection<int, TopicEntity> $topics
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    public Collection $topics;

    /**
     * @var Collection<int, ThankEntity> $thanks
     */
    #[OneToMany(targetEntity: ThankEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $thanks;

}