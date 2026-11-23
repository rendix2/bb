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
use Nette\Utils\ImageException;
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

    private const array TYPES = [ImageType::JPEG, ImageType::PNG, ImageType::GIF, ImageType::WEBP];

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ImageStorageInterface $imageStorage,
        private readonly Avatars $avatars,
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
        if (!$upload->isOk()) {
            throw new AvatarException(self::ERROR_UPLOAD);
        }

        if ($upload->getSize() > $this->avatars->getMaxFileSize()) {
            throw new AvatarException(self::ERROR_SIZE);
        }

        $path = $upload->getTemporaryFile();
        // the type from the content (the magic bytes), never the name or the type sent by the client
        $type = Image::detectTypeFromFile($path, $width, $height);

        if ($type === null || !in_array($type, self::TYPES, true)) {
            throw new AvatarException(self::ERROR_TYPE);
        }

        // before decoding: a small file can declare a huge bitmap (decompression bomb)
        if ($width > $this->avatars->getMaxWidth() || $height > $this->avatars->getMaxHeight()) {
            throw new AvatarException(self::ERROR_DIMENSIONS);
        }

        $content = (string) file_get_contents($path);

        if ($type === ImageType::GIF && $this->isAnimatedGif($content)) {
            throw new AvatarException(self::ERROR_ANIMATED);
        }

        try {
            $image = Image::fromString($content);
        } catch (ImageException) {
            throw new AvatarException(self::ERROR_TYPE);
        }

        $image->resize(self::SIZE, self::SIZE, Image::Cover);
        $image->crop('50%', '50%', self::SIZE, self::SIZE);

        return $image->toString(ImageType::WEBP, self::QUALITY);
    }

    /**
     * More than one image in the GIF (its blocks are walked; GD would quietly take the first frame only).
     */
    private function isAnimatedGif(string $content) : bool
    {
        $length = strlen($content);

        if ($length < 13) {
            return false;
        }

        $packed = ord($content[10]);
        // the header (6), the logical screen descriptor (7) and the global color table
        $position = 13 + (($packed & 0x80) !== 0 ? 3 * (2 << ($packed & 0x07)) : 0);
        $frames = 0;

        while ($position < $length) {
            $block = ord($content[$position]);

            if ($block === 0x2C) {
                if (++$frames > 1) {
                    return true;
                }

                if ($position + 10 > $length) {
                    return false;
                }

                $packed = ord($content[$position + 9]);
                // the image descriptor (10), the local color table and the LZW minimum code size (1)
                $position += 10 + (($packed & 0x80) !== 0 ? 3 * (2 << ($packed & 0x07)) : 0) + 1;
            } elseif ($block === 0x21) {
                // an extension: the introducer and the label
                $position += 2;
            } else {
                // the trailer (0x3B) or garbage
                return false;
            }

            // the data sub-blocks up to the terminator
            while ($position < $length && ($size = ord($content[$position])) !== 0) {
                $position += $size + 1;
            }

            $position++;
        }

        return false;
    }
}
