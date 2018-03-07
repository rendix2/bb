<?php

namespace App\Models;

use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksManager
 *
 * @author rendi
 */
class ThanksManager extends Crud\CrudManager
{
    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @param int $forum_id
     *
     * @return array
     */
    public function getThanksByForumId($forum_id)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->where(
                '[thank_forum_id] = %i',
                $forum_id
            )
            ->fetchAll();
    }

    /**
     * @param int $topic_id
     *
     * @return array
     */
    public function getThanksByTopicId($topic_id)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->where(
                '[thank_topic_id] = %i',
                $topic_id
            )
            ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public function getThanksByUserId($user_id)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->where(
                '[thank_user_id] = %i',
                $user_id
            )
            ->fetchAll();
    }

    /**
     * @param int $topic_id
     *
     * @return array
     */
    public function getThanksWithUserInTopic($topic_id)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->as('t')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on(
                '[u.user_id] = [t.thank_user_id]'
            )
            ->where(
                '[t.thank_topic_id] = %i',
                $topic_id
            )
            ->fetchAll();
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $user_id
     *
     * @return bool
     */
    public function canUserThank($forum_id, $topic_id, $user_id)
    {
        return !$this->dibi->select('1')
            ->from(self::THANKS_TABLE)
            ->where(
                '[thank_forum_id] = %i',
                $forum_id
            )
            ->where(
                '[thank_topic_id] = %i',
                $topic_id
            )
            ->where(
                '[thank_user_id] = %i',
                $user_id
            )
            ->fetch();
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int|void
     */
    public function add(ArrayHash $item_data)
    {
        $this->userManager->update(
            $item_data->thank_user_id,
            ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1'])
        );

        parent::add($item_data);
    }

    /**
     * @param int $topic_id
     *
     * @return Result|int
     */
    public function deleteByTopicId($topic_id)
    {
        return $this->dibi->delete($this->getTable())
            ->where(
                '[thank_topic_id] = %i',
                $topic_id
            )
            ->execute();
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }
}
