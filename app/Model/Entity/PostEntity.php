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
#[Table(name: 'post', options: ['comment' => 'Posts'])]
class PostEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'posts', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'posts', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Category ID'])]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'posts', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'posts', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Topic ID'])]
    public TopicEntity $topic;

    #[Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Title'])]
    public ?string $title;

    #[Column(type: Types::TEXT, nullable: false, options: ['comment' => 'Text'])]
    public string $text;

    #[Column(type: IpAddressType::NAME, nullable: false, options: ['comment' => 'Add IP address'])]
    public string $addIpAddress;

    #[Column(type: IpAddressType::NAME, nullable: true, options: ['comment' => 'Edit IP address'])]
    public ?string $editIpAddress;

    // a locked post cannot be edited or deleted by its author (only by a moderator)
    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is locked?'])]
    public bool $isLocked;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PostHistoryEntity> $historyPosts
     */
    #[OneToMany(targetEntity: PostHistoryEntity::class, mappedBy: 'post', cascade: ['persist', 'remove'],)]
    public Collection $historyPosts;

    /**
     * @var Collection<int, FileEntity> $files
     */
    #[OneToMany(targetEntity: FileEntity::class, mappedBy: 'post', cascade: ['persist', 'remove'])]
    public Collection $files;

    public function __construct()
    {
        $this->isLocked = false;
        $this->uuid = Uuid::uuid4();

        $this->historyPosts = new ArrayCollection();
        $this->files = new ArrayCollection();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}