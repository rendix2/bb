<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use App\Settings\Avatars;
use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Tracy\Debugger;

/**
 * Description of UserManager
 *
 * @author rendix2
 * @package App\Models
 */
class UsersManager extends CrudManager
{

    /**
     * @var int
     */
    const NOT_UPLOADED = -5;
    
    /**
     * @var Avatars $avatars
     */
    private $avatars;

    /**
     * UsersManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     * @param Avatars    $avatars
     */
    public function __construct(
        Connection $dibi,
        IStorage   $storage,
        Avatars    $avatars
    ) {
        parent::__construct($dibi, $storage);

        $this->avatars = $avatars;
    }
    
    /**
     * UsersManager destructor
     */
    public function __destruct()
    {
        $this->avatars = null;
        
        parent::__destruct();
    }

    /**
     * @param int $lang_id
     *
     * @return Row[]
     */
    public function getAllByLang($lang_id)
    {
        return $this->getAllFluent()
            ->where('[user_lang_id] = %i', $lang_id)
            ->fetchAll();
    }

    /**
     * @param string $user_name
     *
     * @return Row|false
     */
    public function getByName($user_name)
    {
        return $this->getAllFluent()
            ->where('[user_name] = %s', $user_name)
            ->fetch();
    }

    /**
     * @param int $role_id
     *
     * @return Row[]
     */
    public function getAllByRole($role_id)
    {
        return $this->getAllFluent()
            ->where('[user_role_id] = %i', $role_id)
            ->fetchAll();
    }

    /**
     * @param int $lang_id
     *
     * @return int
     */
    public function getCountByLang($lang_id)
    {
        return $this->getCountFluent()
            ->where('[user_lang_id] = %i', $lang_id)
            ->fetchSingle();
    }

    /**
     * @param int $role_id
     *
     * @return int
     */
    public function getCountByRole($role_id)
    {
        return $this->getCountFluent()
            ->where('[user_role_id] = %i', $role_id)
            ->fetchSingle();
    }

    /**
     * @return Row|false
     */
    public function getLast()
    {
        return $this->getAllFluent()
            ->where('[user_id] = ', $this->dibi
                ->select('MAX(user_id)')
                ->from($this->getTable()))
            ->fetch();
    }

    /**
     * @param int $user_id
     * @param string $key
     *
     * @return mixed
     */
    public function canBeActivated($user_id, $key)
    {
        return $this->dibi
            ->select('1')
            ->from($this->getTable())
            ->where('[' . $this->getPrimaryKey() . '] = %i', $user_id)
            ->where('[user_activation_key] = %s', $key)
            ->where('[user_active] = %i', 0)
            ->fetchSingle();
    }

    /**
     * @param string $user_name
     *
     * @return Row[]
     */
    public function findLikeByUserName($user_name)
    {
        return $this->getAllFluent()
            ->where('[user_name] LIKE %~like~', $user_name)
            ->fetchAll();
    }

    /**
     *
     * @param string $user_name
     *
     * @return bool
     */
    public function checkUserNameExists($user_name)
    {
        return $this->getAllFluent()
                ->where('[user_name] = %s', $user_name)
                ->fetchSingle() === 1;
    }

    /**
     *
     * @param string $email
     *
     * @return Row|false
     */
    public function getByEmail($email)
    {
        return $this->getAllFluent()
            ->where('[user_email] = %s', $email)
            ->fetch();
    }

    /**
     *
     * @param array $emails
     *
     * @return Row[]
     */
    public function getAllByEmails(array $emails)
    {
        return $this->getAllFluent()
            ->where('[user_email] IN %in', $emails)
            ->fetchAll();
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
            
            $user = $this->getById($user_id);

            if ($user && $user->user_avatar) {
                $this->removeAvatarFile($user->user_avatar);
            }

            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
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
}
