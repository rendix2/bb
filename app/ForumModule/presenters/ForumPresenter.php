<?php

namespace App\ForumModule\Presenters;

use App\Controls\PaginatorControl;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 * @method ForumsManager getManager()
 */
final class ForumPresenter extends Base\ForumPresenter
{

    /**
     * @var CategoriesManager $categoryManager
     */
    private $categoryManager;

    /**
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param CategoriesManager $categoryManager
     */
    public function injectCategoryManager(CategoriesManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * @param     $forum_id
     * @param int $page
     */
    public function renderDefault($forum_id, $page = 1)
    {
        if ( !is_numeric($forum_id) ){
            $this->error('Parameter is not numeric');
        }
        
        $forum = $this->getManager()->getById($forum_id);

        if (!$forum) {
            $this->error('Forum not exists.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        if (!$this->getUser()->isAllowed($forum_id, 'forum_view')) {
            $this->error('Not allowed.');
        }

        $category = $this->categoryManager->getByForumId($forum_id);

        if (!$category) {
            $this->error('Not existing category.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        $topics    = $this->getManager()->getTopics($forum_id);
        $paginator = new PaginatorControl($topics, 10, 5, $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()->getForumsByForumParentId($forum_id);
        $this->template->parentForum = $this->getManager()->getParentForumByForumId($forum_id);
    }
}
