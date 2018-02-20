<?php

namespace App\Models;

use Dibi\Fluent;

/**
 * Description of ReportsManager
 *
 * @author rendi
 */
class ReportsManager extends Crud\CrudManager {

    /**
     * @return Fluent
     */
    public function getAllFluent() {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id');
    }

    /**
     * @param int $forum_id
     *
     * @return Fluent
     */
    public function getByForumId($forum_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_forum_id] = %i', $forum_id);
    }

    /**
     * @param int $topic_id
     *
     * @return Fluent
     */
    public function getByTopicId($topic_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_topic_id] = %i', $topic_id);
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getByUserId($user_id) {
        return parent::getAllFluent()->innerJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_id] = [report_forum_id]')->innerJoin(self::TOPICS_TABLE)->as('t')->on('[report_topic_id] = t.topic_id')->where('[report_user_id] = %i', $user_id);
    }

}
