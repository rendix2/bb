<?php declare(strict_types=1);

namespace App\Model\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The question and the answer (Markdown) of an item of the FAQ in one language (slice 7e2). Deleting a language deletes its texts.
 */
#[Entity()]
#[Table(name: 'faq_translation', options: ['comment' => 'Texts of the FAQ in the languages'])]
// one text per language
#[UniqueConstraint(name: 'UK__Faq_translation__Faq_id_language_id', columns: ['faq_id', 'language_id'])]
class FaqTranslationEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: FaqEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'FAQ ID'])]
    public FaqEntity $faq;

    #[ManyToOne(targetEntity: LanguageEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Language ID'])]
    public LanguageEntity $language;

    #[Column(type: Types::TEXT, options: ['comment' => 'Question'])]
    public string $question;

    #[Column(type: Types::TEXT, options: ['comment' => 'Answer (Markdown)'])]
    public string $answer;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct(FaqEntity $faq, LanguageEntity $language)
    {
        $this->uuid = Uuid::uuid4();
        $this->faq = $faq;
        $this->language = $language;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}
