<?php

namespace App\Models;

use App\Settings\Avatars;
use Dibi\Row;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\FileSystem;

/**
 * Description of UserManager
 *
 * @author rendi
 */
class UsersManager extends Crud\CrudManager
{

    /**
     * @var int
     */
    const NOT_UPLOADED = -5;
    
    /**
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;

    /**
     * @param int $lang_id
     *
     * @return Row[]
     */
    public function getByLang($lang_id)
    {
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
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
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
                ->where('[user_name] = %s', $user_name)
                ->fetch();
    }

    /**
     * @param int $role_id
     *
     * @return Row[]
     */
    public function getByRole($role_id)
    {
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
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
        return $this->dibi
            ->select('COUNT(*)')
            ->from($this->getTable())
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
        return $this->dibi
                ->select('COUNT(*)')
                ->from($this->getTable())
                ->where('[user_role_id] = %i', $role_id)
                ->fetchSingle();
    }

    /**
     * @return Row|false
     */
    public function getLastUser()
    {
        return $this->dibi
            ->query('SELECT * FROM %n WHERE [user_id] = (SELECT MAX(user_id) FROM %n)', $this->getTable(), $this->getTable())
                ->fetch();
    }

    /**
     * @param int    $user_id
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
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
            ->where('[user_name] LIKE %~like~', $user_name)
            ->fetchAll();
    }
    
    /**
     *
     * @param string $user_name
     *
     * @return bool
     */
    public function getByUserName($user_name)
    {
        return $this->dibi
            ->select('')
            ->from($this->getTable())
            ->where('[user_name] = %s', $user_name)
            ->fetchSingle() === 1;
    }
    
    /**
     *
     * @param string $email
     *
     * @return Row[]
     */
    public function getByEmail($email)
    {
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
                ->where('[user_email] = %s', $email)
                ->fetchAll();
    }
    
    /**
     *
     * @param array $emails
     *
     * @return Row[]
     */
    public function getByEmails(array $emails)
    {
        return $this->dibi
                ->select('*')
                ->from($this->getTable())
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
            if ($file->getSize() > $this->avatar->getMaxFileSize()) {
                throw new InvalidArgumentException('File is too big. Max enabled file size is: '.$this->avatar->getMaxFileSize());
            }
                                   
            if ($file->getImageSize()[0] > $this->avatar->getMaxWidth()) {
                throw new InvalidArgumentException('Image width is too big. Max enabled width is: ' .$this->avatar->getMaxWidth());
            }
            
            if ($file->getImageSize()[1] > $this->avatar->getMaxHeight()) {
                throw new InvalidArgumentException('Image height is too big. Max enabled height is: '.$this->avatar->getMaxHeight());
            }
            
            $user = $this->getById($user_id);

            if ($user && $user->user_avatar) {
                $this->removeAvatarFile($user->user_avatar);
            }

            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
            $name      = $hash . '.' . $extension;

            $file->move($this->avatar->getDir() . DIRECTORY_SEPARATOR . $name);

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
            FileSystem::delete($this->avatar->getDir() . DIRECTORY_SEPARATOR . $avatar_file);
            
            return true;
        } catch (\Nette\IOException $e){
            return false;
        }
    }
}
