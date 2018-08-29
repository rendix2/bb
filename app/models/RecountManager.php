<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 8. 2018
 * Time: 12:15
 */

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Class RecountManager
 *
 * @package App\Models
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class RecountManager extends Manager
{

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
                    $this->dibi->update(self::USERS_TABLE, ArrayHash::from(['user_post_count' => $post->post_count]));
                }
            }
        }
    }
}
