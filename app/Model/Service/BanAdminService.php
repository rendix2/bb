<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\Model\Entity\BanReasonEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Repository\UserRepository;
use App\Model\Security\ForumPrivilege;
use DateTimeImmutable;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * The administration of bans and of their predefined reasons (slice 7c1). A ban has a target (a user, an e-mail, an IP
 * address, any of them together), a scope, a reason and an optional end in the future; an admin cannot be banned.
 */
class BanAdminService
{
    public const string ERROR_TARGET = 'admin-ban.error.target';
    public const string ERROR_UNKNOWN_USER = 'admin-ban.error.unknownUser';
    public const string ERROR_ADMIN = 'admin-ban.error.admin';
    public const string ERROR_EMAIL = 'admin-ban.error.email';
    public const string ERROR_IP = 'admin-ban.error.ip';
    public const string ERROR_REASON = 'admin-ban.error.reason';
    public const string ERROR_EXPIRES = 'admin-ban.error.expires';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly UserRepository $users,
        private readonly BanService $banService,
    ) {
    }

    public function saveBan(
        ?BanEntity $ban,
        ?string $username,
        ?string $email,
        ?string $ipAddress,
        BanScope $scope,
        string $reason,
        ?DateTimeImmutable $expiresAt,
        UserEntity $admin,
    ) : BanEntity {
        $username = $this->optional($username);
        $email = $this->optional($email);
        $ipAddress = $this->optional($ipAddress);
        $reason = Strings::trim($reason);

        if ($username === null && $email === null && $ipAddress === null) {
            throw new AdminException(self::ERROR_TARGET);
        }

        $user = null;

        if ($username !== null) {
            $user = $this->users->findOneByUsername($username) ?? throw new AdminException(self::ERROR_UNKNOWN_USER, ['username' => $username]);

            foreach ($user->roles as $role) {
                if ($role->name === ForumPrivilege::ROLE_ADMIN) {
                    throw new AdminException(self::ERROR_ADMIN);
                }
            }
        }

        if ($email !== null && (!Validators::isEmail($email) || Strings::length($email) > 512)) {
            throw new AdminException(self::ERROR_EMAIL);
        }

        if ($ipAddress !== null && filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            throw new AdminException(self::ERROR_IP);
        }

        if ($reason === '') {
            throw new AdminException(self::ERROR_REASON);
        }

        if ($expiresAt !== null && $expiresAt <= new DateTimeImmutable()) {
            throw new AdminException(self::ERROR_EXPIRES);
        }

        if ($ban === null) {
            $ban = new BanEntity();
            $ban->createdBy = $admin;
            $this->em->persist($ban);
        } else {
            $ban->updatedAt = new DateTimeImmutable();
        }

        $ban->user = $user;
        $ban->email = $email !== null ? mb_strtolower($email) : null;
        $ban->ipAddress = $ipAddress;
        $ban->scope = $scope;
        $ban->reason = $reason;
        $ban->expiresAt = $expiresAt;
        $this->em->flush();
        $this->banService->forget();

        return $ban;
    }

    public function deleteBan(BanEntity $ban) : void
    {
        $this->em->remove($ban);
        $this->em->flush();
        $this->banService->forget();
    }

    public function saveReason(?BanReasonEntity $reason, string $text, int $sortOrder) : BanReasonEntity
    {
        $text = Strings::trim($text);

        if ($text === '') {
            throw new AdminException(self::ERROR_REASON);
        }

        if ($reason === null) {
            $reason = new BanReasonEntity();
            $this->em->persist($reason);
        } else {
            $reason->updatedAt = new DateTimeImmutable();
        }

        $reason->text = $text;
        $reason->sortOrder = $sortOrder;
        $this->em->flush();

        return $reason;
    }

    /**
     * The bans keep their own copy of the text, deleting a reason changes none.
     */
    public function deleteReason(BanReasonEntity $reason) : void
    {
        $this->em->remove($reason);
        $this->em->flush();
    }

    /**
     * @return array<string, string> text => text (the select of the form of a ban fills the reason)
     */
    public function reasonChoices() : array
    {
        $choices = [];

        foreach ($this->em->getRepository(BanReasonEntity::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']) as $reason) {
            $choices[$reason->text] = $reason->text;
        }

        return $choices;
    }

    private function optional(?string $value) : ?string
    {
        $value = $value !== null ? Strings::trim($value) : '';

        return $value !== '' ? $value : null;
    }
}
