<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Caching\Cache;
use Nette\Utils\ArrayHash;

/**
 * Description of PostManager
 *
 * @author rendi
 */
class PostsManager extends Crud\CrudManager
{

    /**
     * @var TopicWatchManager $topicWatchManager
     */
    public $topicWatchManager;
    
    /**
     * @var TopicsManager $topicsManager
     */
    private $topicsManager;
    
    /**
     * @var ForumsManager $forumManager
     */
    private $forumManager;
    
    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @param int $category_id
     *
     * @return mixed
     */
    public function getCountOfPostsInCategory($category_id)
    {
        return $this->dibi->select('COUNT(post_id)')
            ->from($this->getTable())
            ->where('[post_category_id] = %i', $category_id)
            ->fetchSingle();
    }

    /**
     * @param int $forum_id
     *
     * @return mixed
     */
    public function getCountOfPostsInForum($forum_id)
    {
        return $this->dibi
                ->select('COUNT(post_id)')
                ->from($this->getTable())
                ->where('[post_forum_id] = %i', $forum_id)
                ->fetchSingle();
    }

    /**
     * @param int $topic_id
     *
     * @return mixed
     */
    public function getCountOfPostsInTopic($topic_id)
    {
        return $this->dibi
                ->select('COUNT(post_id)')
                ->from($this->getTable())
                ->where('[post_topic_id] = %i', $topic_id)
                ->fetchSingle();
    }

    /**
     * @param int $topic_id
     *
     * @return array
     */
    public function getCountOfUsersByTopicId($topic_id)
    {
        return $this->dibi
                ->select('count(post_user_id) as post_count, post_user_id')
                ->from($this->getTable())
                ->where('[post_topic_id] = %i', $topic_id)
                ->groupBy('post_user_id')
                ->fetchAll();
    }

    /**
     * @param int $topic_id
     * @param int $user_id
     *
     * @return mixed
     */
    public function getCountPostByUserId($topic_id, $user_id)
    {
        return $this->dibi->select('COUNT(*)')
            ->from($this->getTable())
            ->where(
                '[post_topic_id] = %i',
                $topic_id
            )
            ->where(
                '[post_user_id] = %i',
                $user_id
            )
            ->fetchSingle();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getLastPostByForumId($forum_id)
    {
        return $this->dibi->query('SELECT * FROM [' . self::POSTS_TABLES . '] WHERE [post_id] = ( SELECT MAX(post_id) FROM [' . self::POSTS_TABLES . '] WHERE [post_forum_id] = %i )', $forum_id)
                ->fetch();
    }

    /**
     * @param int $topic_id
     *
     * @return Row|false
     */
    public function getLastPostByTopicId($topic_id)
    {
        return $this->dibi->query(
            'SELECT * FROM [' . self::POSTS_TABLES . '] WHERE [post_id] = ( SELECT MAX(post_id) FROM [' . self::POSTS_TABLES . '] WHERE [post_topic_id] = %i )',
            $topic_id
        )->fetch();
    }

    /**
     * @param int $forum_id
     * @param int $post_time
     *
     * @return array|mixed
     */
    public function getNewerPosts($forum_id, $post_time)
    {
        $cache  = new Cache($this->storage,$this->getTable());
        $key    = $forum_id . '-' . $post_time;
        $cached = $cache->load($key);

        if (!isset($cached)) {
            $cache->save($key,
                $cached = $this->dibi->select('*')
                    ->from($this->getTable())
                    ->where('[post_forum_id] = %i', $forum_id)
                    ->where('[post_add_time] > %i', $post_time)
                    ->fetchAll(),
                [
                    Cache::EXPIRE => '2 hours',
                ]
            );
        }

        return $cached;
    }

    /**
     * @param int $topic_id
     *
     * @return Fluent
     */
    public function getPostsByTopicId($topic_id)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->as('p')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[p.post_user_id] = [u.user_id]')
            ->where('[post_topic_id] = %i', $topic_id);
    }

    /**
     *
     */
    public function set()
    {
        $this->userManager = new UsersManager($this->dibi);
        $this->userManager->factory($this->getStorage());
        $this->topicsManager = new TopicsManager($this->dibi);
        $this->topicsManager->factory($this->getStorage());
        $this->topicsManager->injectUserManager($this->userManager);
        $this->forumManager = new ForumsManager($this->dibi);
        $this->forumManager->factory($this->getStorage());
        $this->topicWatchManager = new TopicWatchManager(
            $this->dibi,
            $this->topicsManager,
            $this->userManager
        );
        //$this->topicWatchManager->factory($this->getStorage());
    }

    /**
     * @param ArrayHash $item_data
     *
     * @return Result|int
     */
    public function add(ArrayHash $item_data)
    {
        $post_id  = parent::add($item_data);
        $user_id  = $item_data->post_user_id;
        $forum_id = $item_data->post_forum_id;

        $this->topicsManager->update(
            $item_data->post_topic_id,
            ArrayHash::from(['topic_post_count%sql' => 'topic_post_count+1'])
        );

        $topicWatching = $this->topicWatchManager->fullCheck($item_data->post_topic_id, $user_id);

        $watch = [];

        if (!$topicWatching) {
            $this->topicWatchManager->add([$user_id],$item_data->post_topic_id);
            $watch = ['user_watch_count%sql' => 'user_watch_count + 1'];
        }

        $this->userManager->update(
            $user_id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count + 1'] + $watch)
        );

        return $post_id;
    }

    /**
     * @param int $item_id
     *
     * @return Result|int
     */
    public function delete($item_id)
    {
        $post = $this->getById($item_id);
        $res  = parent::delete($item_id);

        $this->userManager->update(
            $post->post_user_id,
            ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1'])
        );
        $this->topicsManager->update(
            $post->post_topic_id,
            ArrayHash::from(['topic_post_count%sql' => 'topic_post_count-1'])
        );

        $postCount = $this->getCountOfUsersByTopicId($post->post_topic_id);

        foreach ($postCount as $ps) {
            if ($ps->post_count === 1 || $ps->post_count === 0) {
                $check = $this->topicWatchManager->fullCheck($post->post_topic_id,$ps->post_user_id);

                $this->topicWatchManager->fullDelete($post->post_topic_id, $post->post_user_id);
            }
        }

        return $res;
    }

    /**
     * @param int $topic_id
     *
     * @return Result|int
     */
    public function deleteByTopicId($topic_id)
    {
        return $this->dibi->delete($this->getTable())->where('[post_topic_id] = %i', $topic_id)->execute();
    }

    /**
     * @param string $post_text
     *
     * @return array
     */
    public function findPosts($post_text)
    {
        return $this->dibi->select('*')
            ->from($this->getTable())
            ->as('p')
            ->leftJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[p.post_topic_id] = [t.topic_id]')
            ->where('MATCH([p.post_title],[p.post_text]) AGAINST(%s IN BOOLEAN MODE)', $post_text)
            ->fetchAll();
    }
}
