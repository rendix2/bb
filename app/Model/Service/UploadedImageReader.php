<?php declare(strict_types=1);

namespace App\Model\Service;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;

/**
 * Reads an uploaded image safely (avatars, images of ranks): the type from the content (the magic bytes), never the name or
 * the type sent by the client; the size of the file and the dimensions are checked before decoding (a small file can declare
 * a huge bitmap); an animated GIF is refused (GD would quietly take the first frame). The error is one of the ERROR_* codes,
 * the caller turns it into its own message.
 */
class UploadedImageReader
{
    public const string ERROR_UPLOAD = 'upload';
    public const string ERROR_SIZE = 'size';
    public const string ERROR_TYPE = 'type';
    public const string ERROR_DIMENSIONS = 'dimensions';
    public const string ERROR_ANIMATED = 'animated';

    private const array TYPES = [ImageType::JPEG, ImageType::PNG, ImageType::GIF, ImageType::WEBP];

    /**
     * @throws UploadedImageException
     */
    public function read(FileUpload $upload, int $maxFileSize, int $maxWidth, int $maxHeight) : Image
    {
        if (!$upload->isOk()) {
            throw new UploadedImageException(self::ERROR_UPLOAD);
        }

        if ($upload->getSize() > $maxFileSize) {
            throw new UploadedImageException(self::ERROR_SIZE);
        }

        $path = $upload->getTemporaryFile();
        $type = Image::detectTypeFromFile($path, $width, $height);

        if ($type === null || !in_array($type, self::TYPES, true)) {
            throw new UploadedImageException(self::ERROR_TYPE);
        }

        if ($width > $maxWidth || $height > $maxHeight) {
            throw new UploadedImageException(self::ERROR_DIMENSIONS);
        }

        $content = (string) file_get_contents($path);

        if ($type === ImageType::GIF && $this->isAnimatedGif($content)) {
            throw new UploadedImageException(self::ERROR_ANIMATED);
        }

        try {
            return Image::fromString($content);
        } catch (ImageException) {
            throw new UploadedImageException(self::ERROR_TYPE);
        }
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
