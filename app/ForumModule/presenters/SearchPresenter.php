<?php

namespace App\ForumModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Forms\SearchPostForm;
use App\Forms\SearchTopicForm;
use App\Forms\SearchUserForm;
use App\Model\Repository\TopicRepository;
use App\Models\UsersManager;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

/**
 * Description of SearchPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\ForumModule\Presenters
 */
class SearchPresenter extends Presenter
{

    public function __construct(

        private readonly Translator $translator,

        private readonly TopicRepository $topicRepository,
    )
    {
        parent::__construct();
    }

    /**
     * SearchPresenter startup.
     */
    public function renderDefault(): void
    {
    }

    /**
     * @param string $q
     */
    public function renderPostResults($q)
    {
        $topics = $this->postsManager->findPostsJoinedTopic($q);

        if (!$topics) {
            $this->flashMessage('Post was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchPostForm-searchPostForm']->setDefaults(['search_post' => $q]);

        $this->template->posts = $topics;
    }

    /**
     * @param string $q
     */
    public function renderTopicResults(string $q): void
    {
        $topics = $this->topicsManager->findByTopicNameJoinedUser($q);

        if (!$topics) {
            $this->flashMessage('Topics was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchTopicForm-searchTopicForm']->setDefaults(['search_topic' => $q]);

        $this->getTemplate()->topics = $topics;
    }

    /**
     * @param string $q
     */
    public function renderUserResults(string $q): void
    {
        $users = $this->getManager()->findLikeByUserName($q);

        if (!$users) {
            $this->flashMessage('User was not found.', self::FLASH_MESSAGE_WARNING);
        }

        $this['searchUserForm-searchUserForm']->setDefaults(['search_user' => $q]);

        $this->getTemplate()->users = $users;
    }
    

    public function createComponentSearchPostForm(): SearchPostForm
    {
        return new SearchPostForm($this->translator);
    }

    public function createComponentSearchTopicForm(): SearchTopicForm
    {
        return new SearchTopicForm($this->translator);
    }

    public function createComponentSearchUserForm(): SearchUserForm
    {
        return new SearchUserForm($this->translator);
    }

    protected function createComponentBreadCrumbDefault(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_search']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbPostResults(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',  'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_post']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbTopicResults(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',  'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_topic']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbUserResults(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',  'text' => 'menu_index'],
            1 => ['link' => 'Search:default', 'text' => 'menu_search'],
            2 => ['text' => 'menu_user']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
