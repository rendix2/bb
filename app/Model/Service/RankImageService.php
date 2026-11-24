<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use App\Settings\RanksDir;
use Contributte\Imagist\Entity\PersistentImage;
use Contributte\Imagist\Entity\StorableImage;
use Contributte\Imagist\Filter\StringFilter\StringFilter;
use Contributte\Imagist\ImageStorageInterface;
use Contributte\Imagist\Scope\Scope;
use Contributte\Imagist\Uploader\StringUploader;
use Nette\Http\FileUpload;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Throwable;

/**
 * The image of a rank (slice 7d), like an avatar (UserAvatarService): read safely (UploadedImageReader), re-encoded to WebP
 * (no metadata, no appended code), stored by contributte/imagist outside www/ (scope ranks) with the thumbnail "rank"
 * (at most 200 x 60 px) made right away; served by Web:Image.
 */
class RankImageService
{
    public const string SCOPE = 'ranks';
    public const string FILTER = 'rank';
    public const int QUALITY = 90;

    public const string ERROR_UPLOAD = 'admin-rank.error.image';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ImageStorageInterface $imageStorage,
        private readonly RanksDir $ranks,
        private readonly UploadedImageReader $imageReader,
    ) {
    }

    /**
     * @throws AdminException
     */
    public function change(RankEntity $rank, FileUpload $upload) : void
    {
        try {
            $webp = $this->imageReader->read($upload, $this->ranks->getMaxFileSize(), $this->ranks->getMaxWidth(), $this->ranks->getMaxHeight())
                ->toString(ImageType::WEBP, self::QUALITY);
        } catch (UploadedImageException $exception) {
            throw new AdminException(self::ERROR_UPLOAD . '.' . $exception->getMessage());
        }

        $image = $this->imageStorage->persist(
            new StorableImage(new StringUploader($webp), Random::generate(32) . '.webp', new Scope(self::SCOPE)),
        );
        $old = $rank->image;

        try {
            $this->imageStorage->persist($image->withFilter(new StringFilter(self::FILTER)));
            $rank->image = $image->getId();
            $this->em->flush();
        } catch (Throwable $exception) {
            $this->removeFiles($image->getId());

            throw $exception;
        }

        $this->removeFiles($old);
    }

    public function delete(RankEntity $rank) : void
    {
        $old = $rank->image;

        if ($old === null) {
            return;
        }

        $rank->image = null;
        $this->em->flush();
        $this->removeFiles($old);
    }

    /**
     * The original and the thumbnail from the disk (also after the rank was deleted).
     */
    public function removeFiles(?string $image) : void
    {
        if ($image === null || !str_starts_with($image, self::SCOPE . '/')) {
            return;
        }

        $this->imageStorage->remove(new PersistentImage($image));
    }
}
