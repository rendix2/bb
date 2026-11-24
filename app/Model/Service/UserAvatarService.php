<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Settings\Avatars;
use Contributte\Imagist\Entity\PersistentImage;
use Contributte\Imagist\Entity\StorableImage;
use Contributte\Imagist\Filter\StringFilter\StringFilter;
use Contributte\Imagist\ImageStorageInterface;
use Contributte\Imagist\Scope\Scope;
use Contributte\Imagist\Uploader\StringUploader;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Throwable;

/**
 * Avatars of users (UserEntity::$avatar = the id of contributte/imagist, "avatars/<32 characters>.webp").
 *
 * The upload is judged by its CONTENT, only static images: an animated GIF is refused (decision of the user), the
 * dimensions are checked before the image is decoded. The image is never stored as uploaded: it is decoded and re-encoded
 * to a 256x256 WebP, which drops EXIF (GPS), data appended after the image (a PHP "polyglot") and the animation. The
 * thumbnails (filters avatar128 and avatar48 of config/ext/imagist.neon) are made right away, so a template never
 * generates them. Files are outside www/, served by the presenter Web:Image.
 */
class UserAvatarService
{
    public const string SCOPE = 'avatars';
    /** @var list<string> the thumbnails made on upload (and served by Web:Image) */
    public const array FILTERS = ['avatar128', 'avatar48'];
    public const int SIZE = 256;
    public const int QUALITY = 85;

    public const string ERROR_UPLOAD = 'web-user-edit.avatar.error.upload';
    public const string ERROR_SIZE = 'web-user-edit.avatar.error.size';
    public const string ERROR_TYPE = 'web-user-edit.avatar.error.type';
    public const string ERROR_DIMENSIONS = 'web-user-edit.avatar.error.dimensions';
    public const string ERROR_ANIMATED = 'web-user-edit.avatar.error.animated';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ImageStorageInterface $imageStorage,
        private readonly Avatars $avatars,
        private readonly UploadedImageReader $imageReader,
    ) {
    }

    /**
     * Stores the new avatar of the user and deletes the old one (with its thumbnails).
     *
     * @throws AvatarException with a translation key
     */
    public function change(UserEntity $user, FileUpload $upload) : void
    {
        $webp = $this->reencode($upload);

        $image = $this->imageStorage->persist(
            new StorableImage(new StringUploader($webp), Random::generate(32) . '.webp', new Scope(self::SCOPE)),
        );

        $old = $user->avatar;

        try {
            foreach (self::FILTERS as $filter) {
                $this->imageStorage->persist($image->withFilter(new StringFilter($filter)));
            }

            $user->avatar = $image->getId();
            $this->em->persist($user);
            $this->em->flush();
        } catch (Throwable $exception) {
            $this->removeFiles($image->getId());

            throw $exception;
        }

        $this->removeFiles($old);
    }

    public function delete(UserEntity $user) : void
    {
        $old = $user->avatar;

        if ($old === null) {
            return;
        }

        $user->avatar = null;
        $this->em->persist($user);
        $this->em->flush();

        $this->removeFiles($old);
    }

    /**
     * Deletes the original and all thumbnails of the avatar from the disk (also after the user was deleted).
     */
    public function removeFiles(?string $avatar) : void
    {
        if ($avatar === null || !str_starts_with($avatar, self::SCOPE . '/')) {
            return;
        }

        $this->imageStorage->remove(new PersistentImage($avatar));
    }

    /**
     * @return string the static 256x256 WebP
     * @throws AvatarException
     */
    private function reencode(FileUpload $upload) : string
    {
        try {
            $image = $this->imageReader->read($upload, $this->avatars->getMaxFileSize(), $this->avatars->getMaxWidth(), $this->avatars->getMaxHeight());
        } catch (UploadedImageException $exception) {
            throw new AvatarException(match ($exception->getMessage()) {
                UploadedImageReader::ERROR_SIZE => self::ERROR_SIZE,
                UploadedImageReader::ERROR_TYPE => self::ERROR_TYPE,
                UploadedImageReader::ERROR_DIMENSIONS => self::ERROR_DIMENSIONS,
                UploadedImageReader::ERROR_ANIMATED => self::ERROR_ANIMATED,
                default => self::ERROR_UPLOAD,
            });
        }

        $image->resize(self::SIZE, self::SIZE, Image::Cover);
        $image->crop('50%', '50%', self::SIZE, self::SIZE);

        return $image->toString(ImageType::WEBP, self::QUALITY);
    }
}
