<?php declare(strict_types=1);

namespace App\services;

use App\Model\Repository\UserRepository;
use App\Settings\Avatars;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use SplFileInfo;
use Tracy\Debugger;

class AvatarService
{
    public function __construct(
        private readonly Avatars $avatars,

        private readonly UserRepository $userRepository,
    )
    {
    }

    /**
     * @param FileUpload $file
     * @param int        $user_id
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function moveAvatar(FileUpload $file, $user_id)
    {
        if ($file->ok) {
            if ($file->getSize() > $this->avatars->getMaxFileSize()) {
                throw new InvalidArgumentException('File is too big. Max enabled file size is: '.$this->avatars->getMaxFileSize());
            }

            if ($file->getImageSize()[0] > $this->avatars->getMaxWidth()) {
                throw new InvalidArgumentException('Image width is too big. Max enabled width is: ' .$this->avatars->getMaxWidth());
            }

            if ($file->getImageSize()[1] > $this->avatars->getMaxHeight()) {
                throw new InvalidArgumentException('Image height is too big. Max enabled height is: '.$this->avatars->getMaxHeight());
            }

            $userEntity = $this->userRepository->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

            if ($userEntity && $userEntity->user_avatar) {
                $this->removeAvatarFile($userEntity->user_avatar);
            }

            $extension = self::getFileExtension($file->name);
            $hash      = Random::generate(32);
            $name      = $hash . '.' . $extension;

            $file->move($this->avatars->getDir() . DIRECTORY_SEPARATOR . $name);

            return $name;
        } else {
            return self::NOT_UPLOADED;
        }
    }

    /**
     *
     * @param string $avatar_file
     *
     * @return bool success
     */
    public function removeAvatarFile($avatar_file)
    {
        try {
            FileSystem::delete($this->avatars->getDir() . DIRECTORY_SEPARATOR . $avatar_file);

            return true;
        } catch (IOException $e) {
            Debugger::log(sprintf('File %s was not deleted.', $this->avatars->getDir() . DIRECTORY_SEPARATOR . $avatar_file));
            return false;
        }
    }

    /**
     * returns extension of file
     *
     * @param string $fileName file name
     *
     * @return string
     * @api
     */
    public static function getFileExtension($fileName)
    {
        $file = new SplFileInfo($fileName);

        return $file->getExtension();
    }
}