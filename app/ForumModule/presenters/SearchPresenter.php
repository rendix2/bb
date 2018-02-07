<?php

namespace App\ForumModule\Presenters;

/**
 * Description of SearchPresenter
 *
 * @author rendi
 * @method \App\Models\UsersManager getManager()
 */
class SearchPresenter extends Base\ForumPresenter {

    private $topicsManager;
    private $postsManager;

    public function __construct(\App\Models\UsersManager $userManager) {
        parent::__construct($userManager);
    }

    public function injectPostsManager(\App\Models\PostsManager $postsManager) {
        $this->postsManager = $postsManager;
    }

    public function injectTopicsManager(\App\Models\TopicsManager $topicsManager) {
        $this->topicsManager = $topicsManager;
    }

    public function renderDefault() {
        
    }

    public function renderUserResults($q) {
        $result = $this->getManager()->findUsersByUserName($q);

        $this['searchUserForm']->setDefaults(['search_user' => $q]);

        if (!$result) {
            $this->flashMessage('User was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->userData = $result;
    }

    public function renderTopicResults($q) {
        $result = $this->topicsManager->findTopicsByTopicName($q);

        $this['searchTopicForm']->setDefaults(['search_topic' => $q]);

        if (!$result) {
            $this->flashMessage('Topics was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->topicData = $result;
    }

    public function renderPostResults($q) {
        $result = $this->postsManager->findPosts($q);

        $this['searchPostForm']->setDefaults(['search_post' => $q]);

        if (!$result) {
            $this->flashMessage('Post was not found.', self::FLASH_MESSAGE_WARNING);
            $result = [];
        }

        $this->template->postData = $result;
    }

    public function createComponentSearchUserForm() {
        $form = new \App\Controls\BootstrapForm();

        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_user', 'User')->setRequired('Please enter name.');
        $form->addSubmit('send_user', 'Search user');
        $form->onSuccess[] = [$this, 'searchUserFormSuccess'];

        return $form;
    }

    public function createComponentSearchTopicForm() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_topic', 'Topic')->setRequired('Please enter some in topic');
        $form->addSubmit('send_topic', 'Search topic');
        $form->onSuccess[] = [$this, 'searchTopicFormSuccess'];

        return $form;
    }

    public function createComponentSearchPostForm() {
        $form = new \App\Controls\BootstrapForm();

        $form->setTranslator($this->getForumTranslator());

        $form->addText('search_post', 'Post')->setRequired('Please enter some in post');
        $form->addSubmit('send_post', 'Search post');
        $form->onSuccess[] = [$this, 'searchPostFormSuccess'];

        return $form;
    }

    public function searchUserFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $this->redirect('Search:userResults', $values->search_user);
    }

    public function searchTopicFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $this->redirect('Search:topicResults', $values->search_topic);
    }

    public function searchPostFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $this->redirect('Search:postResults', $values->search_post);
    }

}
