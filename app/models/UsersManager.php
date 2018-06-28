<?php

namespace App\Models;

use App\Controls\Avatars;
use Dibi\Fluent;
use Dibi\Result;
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
     * @vat string
     */
    const AVATAR_FOLDER = 'avatars';

    /**
     * @var int
     */
    const NOT_UPLOADED = -5;
    /**
     * @var Avatars $avatar
     */
    private $avatar;

    /**
     * @param string $email
     *
     * @return mixed
     */
    public function getByEmail($email)
    {
        return $this->dibi
                ->select('1')
                ->from($this->getTable())
                ->where('[user_email] = %s', $email)
                ->fetchSingle();
    }

    /**
     * @param int $lang_id
     *
     * @return array
     */
    public function getByLangId($lang_id)
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
     * @return array
     */
    public function getByRoleId($role_id)
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
    public function getCountByLangId($lang_id)
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
    public function getCountByRoleId($role_id)
    {
        return $this->dibi
                ->select('COUNT(*)')
                ->from($this->getTable())
                ->where('[user_role_id] = %i', $role_id)
                ->fetchSingle();
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getForumsPermissionsByUserThroughGroup($user_id)
    {
        return $this->dibi
                ->select('*')
                ->from(self::USERS2GROUPS_TABLE)
                ->as('ug')
                ->innerJoin(self::FORUMS2GROUPS_TABLE)
                ->as('fg')
                ->on('[ug.group_id] = [fg.group_id]')
                ->where('[ug.user_id] = %i', $user_id)
                ->fetchAll();
    }

    /**
     * @return Row|false
     */
    public function getLastUser()
    {
        return $this->dibi
            ->query('SELECT * FROM [' . self::USERS_TABLE . '] WHERE [user_id] = (SELECT MAX(user_id) FROM [' . self::USERS_TABLE . '])')
                ->fetch();
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getPosts($user_id)
    {
        return $this->dibi
                ->select('*')
                ->from(self::POSTS_TABLES)
                ->where('[post_user_id] = %i', $user_id);
    }


    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getThanks($user_id)
    {
        return $this->dibi
                ->select('*')
                ->from(self::THANKS_TABLE)
                ->as('th')
                ->innerJoin(self::TOPICS_TABLE)
                ->as('to')
                ->on('[th.thank_topic_id] = [to.topic_id]')
                ->where('[th.thank_user_id] = %i', $user_id);
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getTopics($user_id)
    {
        return $this->dibi->select('*')
                ->from(self::TOPICS_TABLE)
                ->where('[topic_user_id] = %i', $user_id);
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
     * @param int $item_id
     *
     * @return Result|int|void
     */
    public function delete($item_id)
    {
        parent::delete($item_id);

        // TODO
    }

    /**
     * @param int    $id
     * @param string $wwwDir
     */
    public function deletePreviousAvatarFile($id, $wwwDir)
    {
        $user      = $this->getById($id);
        $separator = DIRECTORY_SEPARATOR;

        if ($user) {
            FileSystem::delete($wwwDir . $separator . self::AVATAR_FOLDER . $separator . $user->user_avatar);
        }
    }

    /**
     * @param string $user_name
     *
     * @return array
     */
    public function findUsersByUserName($user_name)
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
            ->where('[user_name] LIKE %~like~', $user_name)
            ->fetchAll();
    }

    /**
     * @param Avatars $avatar
     */
    public function injectAvatars(Avatars $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @param FileUpload $file
     * @param int        $id
     * @param string     $wwwDir
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function moveAvatar(FileUpload $file, $id, $wwwDir)
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
            
            $this->deletePreviousAvatarFile($id, $wwwDir);

            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
            $separator = DIRECTORY_SEPARATOR;
            $name      = $hash . '.' . $extension;

            $file->move($wwwDir . $separator . 'avatars' . $separator . $name);

            return $name;
        } else {
            return self::NOT_UPLOADED;
        }
    }
}
