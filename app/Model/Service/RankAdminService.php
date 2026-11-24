<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use DateTimeImmutable;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

/**
 * The administration of ranks (slice 7d): a rank by posts has a minimum of posts (unique, a user gets the rank of the highest
 * minimum he reached), a special rank has none and the admin gives it to a user (Admin:User:Forum). The image is optional.
 */
class RankAdminService
{
    public const string ERROR_NAME = 'admin-rank.error.name';
    public const string ERROR_MIN_POSTS = 'admin-rank.error.minPosts';
    public const string ERROR_MIN_POSTS_TAKEN = 'admin-rank.error.minPostsTaken';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly RankImageService $rankImage,
    ) {
    }

    public function save(?RankEntity $rank, string $name, ?int $minPosts, ?FileUpload $image, bool $deleteImage) : RankEntity
    {
        $name = Strings::trim($name);

        if ($name === '' || Strings::length($name) > 255) {
            throw new AdminException(self::ERROR_NAME);
        }

        if ($minPosts !== null && $minPosts < 0) {
            throw new AdminException(self::ERROR_MIN_POSTS);
        }

        if ($minPosts !== null) {
            $taken = $this->em->getRepository(RankEntity::class)->findOneBy(['minPosts' => $minPosts]);

            if ($taken !== null && $taken->id !== $rank?->id) {
                throw new AdminException(self::ERROR_MIN_POSTS_TAKEN, ['minPosts' => $minPosts, 'name' => $taken->name]);
            }
        }

        if ($rank === null) {
            $rank = new RankEntity();
            $this->em->persist($rank);
        } else {
            $rank->updatedAt = new DateTimeImmutable();
        }

        $rank->name = $name;
        $rank->minPosts = $minPosts;
        $this->em->flush();

        if ($image !== null && $image->hasFile()) {
            $this->rankImage->change($rank, $image);
        } elseif ($deleteImage) {
            $this->rankImage->delete($rank);
        }

        return $rank;
    }

    /**
     * The users with this special rank fall back to the rank by posts (users.rank_id SET NULL).
     */
    public function delete(RankEntity $rank) : void
    {
        $image = $rank->image;
        $this->em->remove($rank);
        $this->em->flush();
        $this->rankImage->removeFiles($image);
    }
}
