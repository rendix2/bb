<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Database\Types\IpAddressType;
use App\Model\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: PostRepository::class)]
#[Table(name: 'post')]
class PostEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'posts')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'posts')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'x')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'x')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;

    #[Column(type: Types::TEXT, nullable: true)]
    public ?string $title;

    #[Column(type: Types::TEXT, nullable: false)]
    public string $text;

    #[Column(type: IpAddressType::NAME, nullable: false)]
    public string $addIpAddress;

    #[Column(type: IpAddressType::NAME, nullable: true)]
    public string $editIpAddress;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PostHistoryEntity> $historyPosts
     */
    #[OneToMany(targetEntity: PostHistoryEntity::class, mappedBy: 'post', cascade: ['persist', 'remove'],)]
    public Collection $historyPosts;

    /**
     * @var Collection<int, FileEntity> $files
     */
    #[OneToMany(targetEntity: FileEntity::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    public Collection $files;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->historyPosts = new ArrayCollection();
        $this->files = new ArrayCollection();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }


}