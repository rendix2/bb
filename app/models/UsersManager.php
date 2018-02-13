<?php

namespace App\Models;

use Dibi\Row;
use Nette\Http\FileUpload;

/**
 * Description of UserManager
 *
 * @author rendi
 */
class UsersManager extends Crud\CrudManager
{

    /**
     * @param int $lang_id
     *
     * @return array
     */
    public function getByLangId($lang_id)
    {
        return $this->dibi->select('*')->from($this->getTable())->where('[user_lang_id] = %i', $lang_id)->fetchAll();
    }

    /**
     * @param string $user_name
     *
     * @return Row|false
     */
    public function getByName($user_name)
    {
        return $this->dibi->select('*')->from($this->getTable())->where('[user_name] = %s', $user_name)->fetch();
    }

    /**
     * @param int $role_id
     *
     * @return array
     */
    public function getByRoleId($role_id)
    {
        return $this->dibi->select('*')->from($this->getTable())->where('[user_role_id] = %i', $role_id)->fetchAll();
    }

    /**
     * @param int $lang_id
     *
     * @return mixed
     */
    public function getCountByLangId($lang_id)
    {
        return $this->dibi->select('COUNT(*)')
                          ->from($this->getTable())
                          ->where('[user_lang_id] = %i', $lang_id)
                          ->fetchSingle();
    }

    /**
     * @param int $role_id
     *
     * @return mixed
     */
    public function getCountByRoleId($role_id)
    {
        return $this->dibi->select('COUNT(*)')
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
        return $this->dibi->select('*')
                          ->from(self::USERS2GROUPS_TABLE)
                          ->as('ug')
                          ->innerJoin(self::FORUMS2GROUPS_TABLE)
                          ->as('fg')
                          ->on('[ug.group_id] = [fg.group_id]')
                          ->where('[ug.user_id] = %i', $user_id)
                          ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getPosts($user_id)
    {
        return $this->dibi->select('*')->from(self::POSTS_TABLES)->where('[post_user_id] = %i', $user_id)->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getRoles($user_id)
    {
        return $this->dibi->select('*')
                          ->from(self::USERS2ROLES_TABLE)
                          ->as('ur')
                          ->innerJoin(self::ROLES_TABLE)
                          ->as('r')
                          ->on('[r.role_id] = [ur.role_id]')
                          ->where('[ur.user_id] = %i', $user_id)
                          ->fetchPairs('role_id', 'role_name');
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getThanks($user_id)
    {
        return $this->dibi->select('*')
                          ->from(self::THANKS_TABLE)
                          ->as('th')
                          ->innerJoin(self::TOPICS_TABLE)
                          ->as('to')
                          ->on('[th.thank_topic_id] = [to.topic_id]')
                          ->where('[th.thank_user_id] = %i', $user_id)
                          ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getTopics($user_id)
    {
        return $this->dibi->select('*')->from(self::TOPICS_TABLE)->where('[topic_user_id] = %i', $user_id)->fetchAll();
    }

    /**
     * @param string $user_name
     *
     * @return array
     */
    public function findUsersByUserName($user_name)
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->where('[user_name] LIKE %~like~', $user_name)
                          ->fetchAll();
    }

    /**
     * @param FileUpload $file
     * @param string     $wwwDir
     *
     * @return string
     */
    public function movieAvatar(FileUpload $file, $wwwDir)
    {
        if ($file->ok) {
            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
            $separator = DIRECTORY_SEPARATOR;
            $name      = $hash . '.' . $extension;

            $file->move($wwwDir . $separator . 'avatars' . $separator . $name);

            return $name;
        }
    }

}
