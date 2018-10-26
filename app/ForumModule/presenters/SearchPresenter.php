<?php

namespace App\ForumModule\Presenters;


use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Forms\SearchPostForm;
use App\Forms\SearchTopicForm;
use App\Forms\SearchUserForm;
use App\Models\UsersManager;
use App\Models\TopicsManager;
use App\Models\PostsManager;

/**
 * Description of SearchPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 */
class SearchPresenter extends BaseForumPresenter
{
    /**
     *
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;
    
    /**
     *
     * @var PostsManager $postsManager
     * @inject
     */
    public $postsManager;


    /**
     * SearchPresenter constructor.
     *
     * @param UsersManager $userManager
     */
    public function __construct(UsersManager $userManager)
    {
        parent::__construct($userManager);
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchPostForm()
    {
        return new SearchPostForm($this->getForumTranslator());
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchTopicForm()
    {
        return new SearchTopicForm($this->getForumTranslator());
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchUserForm()
    {
        return new SearchUserForm($this->getForumTranslator());
    }

    /**
     *
     */
    public function renderDefault()
    {
    }

    /**
     * @param string $q
     */
    public function renderPostResults($q)
    {
        $topics = $this->postsManager->findPosts($q);

        if (!$topics) {
            $this->flashMessage('Post was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchPostForm-searchPostForm']->setDefaults(['search_post' => $q]);

        $this->template->posts = $topics;
    }

    /**
     * @param string $q
     */
    public function renderTopicResults($q)
    {
        $topics = $this->topicsManager->findByTopicName($q);

        if (!$topics) {
            $this->flashMessage('Topics was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchTopicForm-searchTopicForm']->setDefaults(['search_topic' => $q]);

        $this->template->topics = $topics;
    }

    /**
     * @param string $q
     */
    public function renderUserResults($q)
    {
        $users = $this->getManager()->findLikeByUserName($q);

        if (!$users) {
            $this->flashMessage('User was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchUserForm-searchUserForm']->setDefaults(['search_user' => $q]);

        $this->template->users = $users;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbDefault()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_search']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbPostResults()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_post']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbTopicResults()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_topic']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbUserResults()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }
}
