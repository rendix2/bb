<?php declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

/**
 * An uploaded image was refused; the message is one of the UploadedImageReader::ERROR_* codes.
 */
class UploadedImageException extends RuntimeException
{
}
