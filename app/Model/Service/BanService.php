<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Security\ForumPrivilege;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * Finds the active ban of a visitor (slice 7c1): by his account, e-mail or IP address, of the asked scope or of the whole web.
 * An admin is never banned (no admin can lock the administration out). The answers are kept for the request.
 */
class BanService
{
    /** @var array<string, ?BanEntity> */
    private array $found = [];

    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
    }

    public function find(?UserEntity $user, ?string $email, ?string $ipAddress, BanScope $scope) : ?BanEntity
    {
        if ($user !== null && $this->isAdmin($user)) {
            return null;
        }

        $email = $email ?? $user?->email;
        $email = $email !== null && Strings::trim($email) !== '' ? mb_strtolower(Strings::trim($email)) : null;
        $ipAddress = $ipAddress !== null && filter_var($ipAddress, FILTER_VALIDATE_IP) !== false ? $ipAddress : null;
        $key = implode('|', [$user?->id, $email, $ipAddress, $scope->value]);

        if (array_key_exists($key, $this->found)) {
            return $this->found[$key];
        }

        $targets = [];
        $query = $this->em->getRepository(BanEntity::class)
            ->createQueryBuilder('_b')
            ->where('(_b.expiresAt IS NULL OR _b.expiresAt > :now)')
            ->andWhere('_b.scope IN (:scopes)')
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('scopes', array_unique([BanScope::Web->value, $scope->value]))
            ->orderBy('_b.expiresAt', 'DESC')
            ->addOrderBy('_b.id', 'DESC')
            ->setMaxResults(1);

        if ($user !== null) {
            $targets[] = '_b.user = :user';
            $query->setParameter('user', $user);
        }

        if ($email !== null) {
            $targets[] = 'LOWER(_b.email) = :email';
            $query->setParameter('email', $email);
        }

        if ($ipAddress !== null) {
            $targets[] = '_b.ipAddress = :ip';
            $query->setParameter('ip', $ipAddress);
        }

        if ($targets === []) {
            return $this->found[$key] = null;
        }

        /** @var ?BanEntity $ban */
        $ban = $query->andWhere('(' . implode(' OR ', $targets) . ')')->getQuery()->getOneOrNullResult();

        return $this->found[$key] = $ban;
    }

    /**
     * After a change of the bans in this request (the administration).
     */
    public function forget() : void
    {
        $this->found = [];
    }

    private function isAdmin(UserEntity $user) : bool
    {
        foreach ($user->roles as $role) {
            if ($role->name === ForumPrivilege::ROLE_ADMIN) {
                return true;
            }
        }

        return false;
    }
}
