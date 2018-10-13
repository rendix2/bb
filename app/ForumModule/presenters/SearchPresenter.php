<?php

namespace App\ForumModule\Presenters;


use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Forms\SearchPostForm;
use App\Forms\SearchTopicForm;
use App\Forms\SearchUserForm;
use App\Models\UsersManager;

/**
 * Description of SearchPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 */
class SearchPresenter extends BaseForumPresenter
{
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
        return new SearchPostForm();
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchTopicForm()
    {
        return new SearchTopicForm();
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchUserForm()
    {
        return new SearchUserForm();
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
        $result = $this->postsManager->findPosts($q);

        if (!$result) {
            $this->flashMessage('Post was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchPostForm-searchPostForm']->setDefaults(['search_post' => $q]);

        $this->template->postData = $result;
    }

    /**
     * @param string $q
     */
    public function renderTopicResults($q)
    {
        $result = $this->topicsManager->findByTopicName($q);

        if (!$result) {
            $this->flashMessage('Topics was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchTopicForm-searchTopicForm']->setDefaults(['search_topic' => $q]);

        $this->template->topicData = $result;
    }

    /**
     * @param string $q
     */
    public function renderUserResults($q)
    {
        $result = $this->getManager()->findLikeByUserName($q);

        if (!$result) {
            $this->flashMessage('User was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchUserForm-searchUserForm']->setDefaults(['search_user' => $q]);

        $this->template->userData = $result;
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
