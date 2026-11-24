<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Core\DefaultLanguageService;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Settings\AppDir;
use Contributte\Translation\Translator;
use Locale;
use Nette\Utils\Finder;

/**
 * The languages of the interface (slice 7d, user decision 2026-11-24): a language is a locale. The admin may add a locale of
 * translation.locales.whitelist that has all the translations (to every *.cs.json of the application its *.<locale>.json);
 * the name comes from intl. A language is never renamed. A language used by a user or the default one is never deleted.
 */
class LanguageAdminService
{
    public const string ERROR_NOT_AVAILABLE = 'admin-language.error.notAvailable';
    public const string ERROR_USED = 'admin-language.error.used';
    public const string ERROR_DEFAULT = 'admin-language.error.default';

    /** the language the translations are complete in, every other one is compared with it */
    public const string REFERENCE_LOCALE = 'cs';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator $translator,
        private readonly DefaultLanguageService $defaultLanguage,
        private readonly AppDir $appDir,
    ) {
    }

    /**
     * The translation files of the reference locale without their counterpart in the locale (paths relative to app/).
     *
     * @return list<string>
     */
    public function missingTranslations(string $locale) : array
    {
        $appDir = rtrim((string) $this->appDir->get(), '/');
        $missing = [];

        foreach (Finder::findFiles('*.' . self::REFERENCE_LOCALE . '.json')->from($appDir) as $file) {
            $counterpart = substr($file->getPathname(), 0, -strlen(self::REFERENCE_LOCALE . '.json')) . $locale . '.json';

            if (!is_file($counterpart)) {
                $missing[] = substr($counterpart, strlen($appDir) + 1);
            }
        }

        sort($missing);

        return $missing;
    }

    /**
     * The locales of the whitelist that are not languages yet: locale => [name, missing translations].
     *
     * @return array<string, array{name: string, missing: list<string>}>
     */
    public function candidates() : array
    {
        $existing = array_map(
            static fn (LanguageEntity $language) : string => $language->locale,
            $this->em->getRepository(LanguageEntity::class)->findAll(),
        );
        $candidates = [];

        foreach ($this->translator->getLocalesWhitelist() ?? [] as $locale) {
            if (!in_array($locale, $existing, true)) {
                $candidates[$locale] = ['name' => $this->nameOf($locale), 'missing' => $this->missingTranslations($locale)];
            }
        }

        return $candidates;
    }

    public function add(string $locale) : LanguageEntity
    {
        $candidate = $this->candidates()[$locale] ?? null;

        if ($candidate === null || $candidate['missing'] !== []) {
            throw new AdminException(self::ERROR_NOT_AVAILABLE, ['locale' => $locale]);
        }

        $language = new LanguageEntity();
        $language->locale = $locale;
        $language->name = $candidate['name'];
        // the old identifier of a language (the parameter defaultLanguage names the default one by it): "slovak"
        $language->fileName = mb_strtolower(Locale::getDisplayLanguage($locale, 'en'));
        $this->em->persist($language);
        $this->em->flush();

        return $language;
    }

    public function delete(LanguageEntity $language) : void
    {
        if ($language->id === $this->defaultLanguage->get()->id) {
            throw new AdminException(self::ERROR_DEFAULT);
        }

        $users = $this->em->getRepository(UserEntity::class)->count(['language' => $language]);

        if ($users > 0) {
            throw new AdminException(self::ERROR_USED, ['users' => $users]);
        }

        $this->em->remove($language);
        $this->em->flush();
    }

    /**
     * "Slovenčina": the name of the language in itself.
     */
    public function nameOf(string $locale) : string
    {
        return mb_convert_case(Locale::getDisplayLanguage($locale, $locale), MB_CASE_TITLE);
    }
}
