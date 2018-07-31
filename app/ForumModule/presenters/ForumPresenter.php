<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\PaginatorControl;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\ModeratorsManager;
use App\Models\TopicsManager;
use App\Settings\TopicsSetting;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;
use Nette\Utils\ArrayHash;

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
     * @inject
     */
    public $categoryManager;

    /**
     * @var TopicsManager $topicManager
     * @inject
     */
    public $topicManager;
    
    /**
     * @var TopicsSetting $topicSetting
     * @inject
     */
    public $topicSetting;

    /**
     *
     * @var ModeratorsManager $moderators
     * @inject
     */
    public $moderators;

    /**
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * renders topics
     *
     * @param int         $forum_id
     * @param int         $page
     * @param string|null $q
     */
    public function renderDefault($forum_id, $page = 1, $q = null)
    {
        if (!is_numeric($forum_id)) {
            $this->error('Parameter is not numeric');
        }

        $forum = $this->getManager()->getById($forum_id);

        if (!$forum) {
            $this->error('Forum does not exist.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        if (!$this->getUser()->isAllowed($forum_id, 'forum_view')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $category = $this->categoryManager->getByForum($forum_id);

        if (!$category) {
            $this->error('Not existing category.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        $topics    = $this->topicManager->getFluentJoinedUsersByForum($forum_id);
        $paginator = new PaginatorControl($topics, 10, 5, $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }
        
        if ($q) {
            $topics = $topics->where('topic_id IN ( SELECT post_topic_id FROM posts WHERE MATCH(post_title, post_text) AGAINST (%s IN BOOLEAN MODE) AND post_forum_id = %i) OR MATCH(topic_name) AGAINST (%s IN BOOLEAN MODE)', $q, $forum_id, $q);                               
            $this['searchInForumForm']->setDefaults(['search_form' => $q]);
        }
        
        $moderators = $this->moderators->getAllJoinedByRight($forum_id);
        
        if (!$moderators) {
            $this->flashMessage('No moderators in forum.', self::FLASH_MESSAGE_INFO);
        }

        $this->template->logViews    = $this->topicSetting->canLogView();
        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()->getByParent($forum_id);
        $this->template->parentForum = $this->getManager()->getParentForumByForumId($forum_id);
        $this->template->moderators  = $moderators;
    }

    /**
     * renderes rules of forum
     * 
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

    /**
     * @param int $forum_id
     */
    public function renderSearchForum($forum_id)
    {
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentSearchInForumForm()
    {
         $form = $this->createBootstrapForm();
         $form->addText('search_form', 'Search forum:');
         $form->addSubmit('submit', 'Search');
         $form->onSuccess[] = [$this, 'searchInForumFormSuccess'];
         
         return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchInForumFormSuccess(Form $form, ArrayHash $values)
    {
        $this->redirect(
            'Forum:default',
            $this->getParameter('forum_id'),
            $this->getParameter('page'),
            $values->search_form
        );
    }
}
