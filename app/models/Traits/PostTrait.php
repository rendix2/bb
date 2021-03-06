<?php

namespace App\Models\Traits;

use App\Models\Entity\PostEntity;
use App\Models\PostsManager;
use Nette\Http\IResponse;

/**
 * Description of PostTrait
 *
 * @author rendix2
 */
trait PostTrait
{

    /**
     *
     * @var PostsManager $manager
     * @inject
     */
    public $postsManager;
        
    /**
     *
     * @param int $post_id
     * @param int $category_id
     * @param int $forum_id
     * @param int $topic_id
     *
     * @return PostEntity
     */
    public function checkPostParam($post_id, $category_id, $forum_id, $topic_id)
    {
        if (!isset($post_id)) {
            $this->error('Post param is not set.');
        }
        
        if (!is_numeric($post_id)) {
            $this->error('Post param is not numeric.');
        }

        $postDibi = $this->postsManager->getById($post_id);

        if (!$postDibi) {
            $this->error('Post was not found.');
        }
        
        $post = PostEntity::setFromRow($postDibi);

        if ($post->getPost_category_id() !== (int)$category_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->getPost_forum_id() !== (int)$forum_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->getPost_topic_id() !== (int)$topic_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->getPost_locked()) {
            $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
        }

        return $post;
    }
}
