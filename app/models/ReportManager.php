<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Fluent;
use Dibi\Result;
use Nette\Utils\ArrayHash;

/**
 * Description of ReportsManager
 *
 * @author rendix2
 * @package App\Models
 */
class ReportManager extends CrudManager
{

    public function getAllFluent()
    {
        return $this->dibi
            ->select('r.*, u.*, f.*, t.*, p.* , u2.user_id AS reported_user_id, u2.user_name as reported_user_name')
            ->from($this->getTable())
            ->as('r')
            ->leftJoin(self::FORUM_TABLE)
            ->as('f')
            ->on('[f.forum_id] = [report_forum_id]')
            ->leftJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[r.report_topic_id] = [t.topic_id]')
            ->leftJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[r.report_user_id] = [u.user_id]')
            ->leftJoin(self::POSTS_TABLE)
            ->as('p')
            ->on('[r.report_post_id] = [p.post_id]')
            ->leftJoin(self::USERS_TABLE)
            ->as('u2')
            ->on('[r.report_reported_user_id] = [u2.user_id]');
    }

    public function deleteByPost($post_id)
    {
        return $this->deleteFluent()
            ->where('[report_post_id] = %i', $post_id)
            ->execute();
    }

    public function deleteByTopic($topic_id)
    {
        return $this->deleteFluent()
            ->where('[report_topic_id] = %i', $topic_id)
            ->execute();
    }

    public function deleteByPosts(array $post_ids)
    {
        return $this->deleteFluent()
            ->where('[report_post_id] IN %in', $post_ids)
            ->execute();
    }

    public function updateByPost($post_id, ArrayHash $item_data)
    {
        return $this->updateFluent($item_data)
            ->where('[report_post_id] = %i', $post_id)
            ->execute();
    }

    public function updateByTopic($topic_id, ArrayHash $item_data)
    {
        return $this->updateFluent($item_data)
            ->where('[report_topic_id] = %i', $topic_id)
            ->execute();
    }
}
