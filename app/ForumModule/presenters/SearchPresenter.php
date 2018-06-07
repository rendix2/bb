<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of SearchPresenter
 *
 * @author rendi
 * @method UsersManager getManager()
 */
class SearchPresenter extends Base\ForumPresenter
{
    /**
     * @var TopicsManager $topicsManager
     */
    private $topicsManager;

    /**
     * @var PostsManager $postsManager
     */
    private $postsManager;

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
        $form = new BootstrapForm();

        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_post', 'Post')->setRequired('Please enter some in post');
        $form->addSubmit('send_post', 'Search post');
        $form->onSuccess[] = [$this, 'searchPostFormSuccess'];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchTopicForm()
    {
        $form = new BootstrapForm();
        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_topic', 'Topic')->setRequired('Please enter some in topic');
        $form->addSubmit('send_topic', 'Search topic');
        $form->onSuccess[] = [$this, 'searchTopicFormSuccess'];

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentSearchUserForm()
    {
        $form = new BootstrapForm();

        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_user', 'User')->setRequired('Please enter name.');
        $form->addSubmit('send_user', 'Search user');
        $form->onSuccess[] = [$this, 'searchUserFormSuccess'];

        return $form;
    }

    /**
     * @param PostsManager $postsManager
     */
    public function injectPostsManager(PostsManager $postsManager)
    {
        $this->postsManager = $postsManager;
    }

    /**
     * @param TopicsManager $topicsManager
     */
    public function injectTopicsManager(TopicsManager $topicsManager)
    {
        $this->topicsManager = $topicsManager;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchPostFormSuccess(Form $form, ArrayHash $values)
    {
        $this->redirect('Search:postResults', $values->search_post);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchTopicFormSuccess(Form $form, ArrayHash $values)
    {
        $this->redirect('Search:topicResults', $values->search_topic);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function searchUserFormSuccess(Form $form, ArrayHash $values)
    {
        $this->redirect('Search:userResults', $values->search_user);
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

        $this['searchPostForm']->setDefaults(['search_post' => $q]);

        if (!$result) {
            $this->flashMessage('Post was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->postData = $result;
    }

    /**
     * @param string $q
     */
    public function renderTopicResults($q)
    {
        $result = $this->topicsManager->findTopicsByTopicName($q);

        $this['searchTopicForm']->setDefaults(['search_topic' => $q]);

        if (!$result) {
            $this->flashMessage('Topics was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->topicData = $result;
    }

    /**
     * @param string $q
     */
    public function renderUserResults($q)
    {
        $result = $this->getManager()->findUsersByUserName($q);

        $this['searchUserForm']->setDefaults(['search_user' => $q]);

        if (!$result) {
            $this->flashMessage('User was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->userData = $result;
    }
}
