<?php declare(strict_types=1);

namespace App\Core;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Settings\DefaultLanguage;
use LogicException;

/**
 * The language a new user gets (users.language_id is NOT NULL): the one named by the parameter defaultLanguage.
 */
class DefaultLanguageService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly DefaultLanguage $defaultLanguage,
    ) {
    }

    public function get() : LanguageEntity
    {
        $fileName = (string) $this->defaultLanguage->get();

        $language = $this->em
            ->getRepository(LanguageEntity::class)
            ->findOneBy(
                [
                    'fileName' => $fileName,
                ]
            );

        if ($language === null) {
            throw new LogicException('The default language "' . $fileName . '" is not in the database.');
        }

        return $language;
    }

}
