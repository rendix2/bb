<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Core\DefaultLanguageService;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\FaqEntity;
use App\Model\Entity\FaqTranslationEntity;
use App\Model\Entity\LanguageEntity;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * The FAQ (slice 7e2): an item has a question and an answer (Markdown) for each language of the table language. The readers
 * get the texts in their language; an item without it (a language added later) falls back to the default language.
 */
class FaqService
{
    public const string ERROR_TEXT = 'admin-faq.error.text';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly DefaultLanguageService $defaultLanguage,
    ) {
    }

    /**
     * @return list<LanguageEntity> the default language first
     */
    public function languages() : array
    {
        $default = $this->defaultLanguage->get();
        $languages = $this->em->getRepository(LanguageEntity::class)->findBy([], ['name' => 'ASC']);
        usort($languages, fn (LanguageEntity $a, LanguageEntity $b) : int => ($b->id === $default->id) <=> ($a->id === $default->id));

        return $languages;
    }

    /**
     * @return array<int, FaqTranslationEntity> by the ID of the language
     */
    public function texts(FaqEntity $faq) : array
    {
        $texts = [];

        foreach ($this->em->getRepository(FaqTranslationEntity::class)->findBy(['faq' => $faq]) as $text) {
            $texts[$text->language->id] = $text;
        }

        return $texts;
    }

    /**
     * @param array<int, array{question: string, answer: string}> $texts by the ID of the language, all languages required
     */
    public function save(?FaqEntity $faq, int $sortOrder, array $texts) : FaqEntity
    {
        $languages = $this->languages();

        foreach ($languages as $language) {
            $question = Strings::trim($texts[$language->id]['question'] ?? '');
            $answer = Strings::trim($texts[$language->id]['answer'] ?? '');

            if ($question === '' || $answer === '') {
                throw new AdminException(self::ERROR_TEXT, ['language' => $language->name]);
            }
        }

        if ($faq === null) {
            $faq = new FaqEntity();
            $this->em->persist($faq);
            $existing = [];
        } else {
            $faq->updatedAt = new DateTimeImmutable();
            $existing = $this->texts($faq);
        }

        $faq->sortOrder = $sortOrder;

        foreach ($languages as $language) {
            $text = $existing[$language->id] ?? null;

            if ($text === null) {
                $text = new FaqTranslationEntity($faq, $language);
                $this->em->persist($text);
            } else {
                $text->updatedAt = new DateTimeImmutable();
            }

            $text->question = Strings::trim($texts[$language->id]['question']);
            $text->answer = Strings::trim($texts[$language->id]['answer']);
        }

        $this->em->flush();

        return $faq;
    }

    public function delete(FaqEntity $faq) : void
    {
        $this->em->remove($faq);
        $this->em->flush();
    }

    /**
     * The items in the order, each in the language of the locale, or in the default language when it has no text in it.
     *
     * @return list<FaqTranslationEntity>
     */
    public function forLocale(string $locale) : array
    {
        $default = $this->defaultLanguage->get();

        /** @var list<FaqTranslationEntity> $rows */
        $rows = $this->em->createQueryBuilder()
            ->select('t', 'f', 'l')
            ->from(FaqTranslationEntity::class, 't')
            ->join('t.faq', 'f')
            ->join('t.language', 'l')
            ->where('l.locale = :locale OR l.id = :default')
            ->setParameter('locale', $locale)
            ->setParameter('default', $default->id)
            ->orderBy('f.sortOrder', 'ASC')
            ->addOrderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();

        $items = [];

        foreach ($rows as $row) {
            if (!isset($items[$row->faq->id]) || $row->language->locale === $locale) {
                $items[$row->faq->id] = $row;
            }
        }

        return array_values($items);
    }
}
