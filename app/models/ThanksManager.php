<?php

namespace App\Models;

/**
 * Description of ThanksMnager
 *
 * @author rendi
 */
class ThanksManager extends Crud\CrudManager {

    private $userManager;

    public function injectUserManager(UsersManager $userManager) {
        $this->userManager = $userManager;
    }

    public function add(\Nette\Utils\ArrayHash $item_data) {
        $this->userManager->update($item_data->thank_user_id, \Nette\Utils\ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1']));

        parent::add($item_data);
    }

    public function canUserThank($forum_id, $topic_id, $user_id) {
        return !$this->dibi->select('1')->from(self::THANKS_TABLE)->where('[thank_forum_id] = %i', $forum_id)->where('[thank_topic_id] = %i', $topic_id)->where('[thank_user_id] = %i', $user_id)->fetch();
    }

    public function getThanksWithUserInTopic($topic_id) {
        return $this->dibi->select('*')->from($this->getTable())->as('t')->innerJoin(self::USERS_TABLE)->as('u')->on('[u.user_id] = [t.thank_user_id]')->where('[t.thank_topic_id] = %i', $topic_id)->fetchAll();
    }

    public function getThanksByTopicId($topic_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[thank_topic_id] = %i', $topic_id)->fetchAll();
    }

    public function getThanksByForumId($forum_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[thank_forum_id] = %i', $forum_id)->fetchAll();
    }

    public function getThanksByUserId($user_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[thank_user_id] = %i', $user_id)->fetchAll();
    }

}
