<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicsManager
 *
 * @author rendix2
 * @package App\Models
 */
class TopicManager extends CrudManager
{




    /**
     *
     * @param int $topic_id
     * @param int|null $target_forum_id
     *
     * @return int
     */
    public function copy($topic_id, $target_forum_id = null)
    {
        $topic = $this->getById($topic_id);
        
        unset($topic->topic_id);
        
        if ($target_forum_id) {
            $topic->topic_forum_id = $target_forum_id;
        }

        return $this->add(ArrayHash::from($topic->toArray()));
    }
}
