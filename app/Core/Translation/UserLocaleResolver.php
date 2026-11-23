<?php declare(strict_types=1);

namespace App\Core\Translation;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\Translator;
use Nette\Security\User;

/**
 * The language of the interface of a logged-in user: the language of his settings (users.language, Web:User:Edit), read from the
 * database (language.locale; not from the identity in the session: a change of the setting applies at the next request). A guest (null) gets the
 * next resolver or the default locale (cs). One query; the translator keeps the resolved locale for the rest of the request.
 */
class UserLocaleResolver implements ResolverInterface
{
    public function __construct(
        private readonly User $user,
        private readonly EntityManagerDecorator $em,
    ) {
    }

    public function resolve(Translator $translator) : ?string
    {
        if (!$this->user->isLoggedIn()) {
            return null;
        }

        $locale = $this->em->getRepository(UserEntity::class)
            ->createQueryBuilder('_u')
            ->select('_l.locale')
            ->join('_u.language', '_l')
            ->where('_u.id = :id')
            ->setParameter('id', $this->user->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!is_array($locale) || !is_string($locale['locale'] ?? null)) {
            return null;
        }

        return $locale['locale'];
    }
}
