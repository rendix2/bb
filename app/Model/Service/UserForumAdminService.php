<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Settings\Users;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * The forum side of a user in the administration (slice 7c2, Admin:User:Forum): the language, the signature and the special
 * rank, and what the forum knows of him (groups, own grants, bans). The account itself is Admin:User:Edit (web-project).
 */
class UserForumAdminService
{
    public const string ERROR_SIGNATURE = 'admin-user-forum.error.signature';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Users $users,
    ) {
    }

    public function signatureMaxLength() : int
    {
        return max(1, (int) $this->users->get()['signatureMaxLength']);
    }

    public function save(UserEntity $user, LanguageEntity $language, ?string $signature, ?RankEntity $rank) : void
    {
        $signature = $signature !== null ? Strings::trim($signature) : '';

        if (Strings::length($signature) > $this->signatureMaxLength()) {
            throw new AdminException(self::ERROR_SIGNATURE, ['maxChars' => $this->signatureMaxLength()]);
        }

        $user->language = $language;
        $user->signature = $signature !== '' ? $signature : null;
        $user->rank = $rank;
        $user->updatedAt = new DateTimeImmutable();
        $this->em->flush();
    }

    /**
     * @return list<GroupEntity>
     */
    public function groups(UserEntity $user) : array
    {
        /** @var list<GroupEntity> */
        return $this->em->getRepository(GroupEntity::class)
            ->createQueryBuilder('_g')
            ->innerJoin(UserGroupMemberEntity::class, '_m', 'WITH', '_m.group = _g')
            ->where('_m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('_g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<UserForumPermissionEntity>
     */
    public function grants(UserEntity $user) : array
    {
        /** @var list<UserForumPermissionEntity> */
        return $this->em->getRepository(UserForumPermissionEntity::class)
            ->createQueryBuilder('_g')
            ->select('_g', '_f')
            ->innerJoin('_g.forum', '_f')
            ->where('_g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('_f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * The bans of his account (also expired), the newest first.
     *
     * @return list<BanEntity>
     */
    public function bans(UserEntity $user) : array
    {
        return $this->em->getRepository(BanEntity::class)->findBy(['user' => $user], ['createdAt' => 'DESC', 'id' => 'DESC']);
    }
}
