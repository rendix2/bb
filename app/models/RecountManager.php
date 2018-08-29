<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 8. 2018
 * Time: 12:15
 */

namespace App\Models;

use Dibi\Connection;
use Nette\Utils\ArrayHash;

/**
 * Class RecountManager
 *
 * @package App\Models
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class RecountManager extends Manager
{
    /**
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * RecountManager constructor.
     *
     * @param Connection   $dibi
     * @param UsersManager $usersManager
     */
    public function __construct(Connection $dibi, UsersManager $usersManager)
    {
        parent::__construct($dibi);

        $this->usersManager = $usersManager;
    }

    public function recountUsersPostCount()
    {
        $posts = $this->dibi
            ->select('COUNT(post_id) as post_count, post_user_id')
            ->from(self::POSTS_TABLE)
            ->groupBy('post_user_id')
            ->orderBy('post_user_id')
            ->fetchAll();

        $users = $this->dibi
            ->select('user_id, user_post_count')
            ->from(self::USERS_TABLE)
            ->orderBy('user_id')
            ->fetchAll();

        foreach ($users as $user) {
            foreach ($posts as $post) {
                if (!$user->user_id !== $post->post_user_id) {
                    continue;
                }

                if ($post->post_count !== $user->user_post_count) {
                    $this->usersManager->update(
                        $user->user_id,
                        ArrayHash::from(['user_post_count' => $post->post_count])
                    );
                }
            }
        }
    }

    public function recountUsersTopicCount()
    {
        $topics = $this->dibi
            ->select('COUNT(topic_id) as topic_count, topic_user_id')
            ->from(self::TOPICS_TABLE)
            ->groupBy('topic_user_id')
            ->orderBy('topic_user_id')
            ->fetchAll();

        $users = $this->dibi
            ->select('user_id, user_topic_count')
            ->from(self::USERS_TABLE)
            ->orderBy('user_id')
            ->fetchAll();

        foreach ($users as $user) {
            foreach ($topics as $topic) {
                if (!$user->user_id !== $topic->topic_user_id) {
                    continue;
                }

                if ($topic->topic_count !== $user->user_topic_count) {
                    $this->usersManager->update(
                        $user->user_id,
                        ArrayHash::from(['user_topic_count' => $topic->topic_count])
                    );
                }
            }
        }
    }

    public function recountUsersTopicWatchCount()
    {
        $topics = $this->dibi
            ->select('COUNT(thank_topic_id) as topic_thank_count, thank_user_id')
            ->from(self::TOPIC_WATCH_TABLE)
            ->groupBy('thank_user_id')
            ->orderBy('thank_user_id')
            ->fetchAll();

        $users = $this->dibi
            ->select('user_id, user_thank_count')
            ->from(self::USERS_TABLE)
            ->orderBy('user_id')
            ->fetchAll();

        foreach ($users as $user) {
            foreach ($topics as $topic) {
                if (!$user->user_id !== $topic->topic_user_id) {
                    continue;
                }

                if ($topic->topic_count !== $user->user_topic_count) {
                    $this->usersManager->update(
                        $user->user_id,
                        ArrayHash::from(['user_thank_count' => $topic->topic_count])
                    );
                }
            }
        }
    }
}
