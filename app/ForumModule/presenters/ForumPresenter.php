<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\PaginatorControl;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\TopicsManager;
use Nette\Http\IResponse;

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
     * @var TopicsManager $topicManager
     */
    private $topicManager;

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
     * @param TopicsManager $topicManager
     */
    public function injectTopicManager(TopicsManager $topicManager)
    {
        $this->topicManager = $topicManager;
    }

    /**
     * @param int $forum_id
     * @param int $page
     */
    public function renderDefault($forum_id, $page = 1, $q = null)
    {
        
        if (!is_numeric($forum_id)) {
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
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
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
        
        if ($q) {
            $topics = $topics->where('topic_id IN ( SELECT post_topic_id FROM posts WHERE MATCH(post_title, post_text) AGAINST (%s IN BOOLEAN MODE) AND post_forum_id = %i) OR MATCH(topic_name) AGAINST (%s IN BOOLEAN MODE)', $q, $forum_id, $q);                               
            $this['searchInForumForm']->setDefaults(['search_form' => $q]);
        }

        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()
            ->getForumsByForumParentId($forum_id);
        $this->template->parentForum = $this->getManager()
            ->getParentForumByForumId($forum_id);
    }

    /**
     * @param int $forum_id
     */
    public function renderRules($forum_id)
    {
        if (!is_numeric($forum_id)) {
            $this->error('Parameter is not numeric');
        }

        $forum = $this->getManager()->getById($forum_id);

        if (!$forum) {
            $this->error('Forum not found');
        }

        $this->template->forum = $forum;
    }
    
    public function renderSearchForum($forum_id)
    {
        
    }

    protected function createComponentSearchInForumForm()
    {
         $form = new BootstrapForm();
         $form->addText('search_form', 'Search forum:');
         $form->addSubmit('submit', 'Search');
         $form->onSuccess[] = [$this, 'searchInForumFormSuccess'];
         
         return $form;
    }
    
    public function searchInForumFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        $this->redirect('Forum:default', $this->getParameter('forum_id'), $this->getParameter('page'), $values->search_form);
    }
}
