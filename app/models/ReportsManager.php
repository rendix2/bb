<?php

namespace App\Models;

/**
 * Description of ReportsManager
 *
 * @author rendi
 */
class ReportsManager extends Crud\CrudManager {

    public function getAllFluent() {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id');
    }

    public function getByForumId($forum_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_forum_id] = %i', $forum_id);
    }

    public function getByTopicId($topic_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_topic_id] = %i', $topic_id);
    }

    public function getByUserId($user_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_user_id] = %i', $user_id);
    }

}
