<?php

namespace App\ForumModule\Presenters;

use Nette\Http;

/**
 * Description of PostPresenter
 *
 * @author rendi
 * @method \App\Models\PostsManager getManager()
 */
class PostPresenter extends Base\ForumPresenter {

    /**
     * @var \App\Models\UsersManager $userManager 
     */
    private $userManager;

    /**
     * @var \App\Models\ForumsManager $forumManager
     */
    private $forumManager;

    /**
     * @var \App\Models\ThanksManager $thanksManager
     */
    private $thanksManager;

    /**
     * @var \App\Models\TopicsManager $topicsManager
     */
    private $topicsManager;

    /**
     * @param \App\Models\PostsManager $manager
     */
    public function __construct(\App\Models\PostsManager $manager) {
        parent::__construct($manager);
    }

    public function injectUsersManager(\App\Models\UsersManager $usersManager) {
        $this->userManager = $usersManager;
    }

    public function injectForumsManager(\App\Models\ForumsManager $forumsManager) {
        $this->forumManager = $forumsManager;
    }

    public function injectThanksManager(\App\Models\ThanksManager $thanksManager) {
        $this->thanksManager = $thanksManager;
    }

    public function injectTopicsManager(\App\Models\TopicsManager $topicsManager) {
        $this->topicsManager = $topicsManager;
    }

    public function actionDeletePost($forum_id, $topic_id, $post_id) {
        $post = $this->getManager()->getById($post_id);

        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post!', Http\IResponse::S403_FORBIDDEN);
        }

        $topic = $this->topicsManager->getById($topic_id);

        $lastPost = $this->getManager()->getLastPostByTopic($topic_id, $post_id);
        $lastPostByForum = $this->getManager()->getLastPostByForum($forum_id, $post_id, $topic_id);

        if ((int) $post_id === $topic->topic_last_post_id) {
            if ($lastPost) {
                $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => $lastPost->post_id, 'topic_last_post_user_id' => $lastPost->post_user_id]));
                $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $lastPostByForum->post_topic_id, 'forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
            } else {
                $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => 0, 'topic_last_post_user_id' => 0]));
                $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $lastPostByForum->post_topic_id, 'forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
            }
        }

        $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_post_count%sql' => 'topic_post_count - 1']));
        $this->getManager()->delete($post_id);
        $this->userManager->update($this->getUser()->getId(), \Nette\Utils\ArrayHash::from(['user_post_count%sql' => 'user_post_count - 1']));

        $this->flashMessage('Post deleted.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Post:all', $forum_id, $topic_id);
    }

    public function actionDeleteTopic($forum_id, $topic_id, $page) {
        $topic = $this->topicsManager->getById($topic_id);
        $forum = $this->forumManager->getById($forum_id);


        $lastPostByForum = $this->getManager()->getLastPostByForum($forum_id, 0, $topic_id);

        if ((int) $topic_id === $forum->forum_last_topic_id) {
            $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => $lastPostByForum->post_id, 'topic_last_post_user_id' => $lastPostByForum->post_user_id]));
            $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $lastPostByForum->post_topic_id, 'forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
        } else {
            $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => $lastPostByForum->post_id, 'topic_last_post_user_id' => $lastPostByForum->post_user_id]));
            $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_post_id' => $lastPostByForum->post_id, 'forum_last_post_user_id' => $lastPostByForum->post_user_id]));
        }

        if ($topic->topic_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of topic!', Http\IResponse::S403_FORBIDDEN);
        }

        $this->topicsManager->delete($topic_id);

        $this->flashMessage('Topic deleted.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Forum:default', $forum_id, $page);
    }

    public function actionThank($forum_id, $topic_id) {
        $user_id = $this->getUser()->getId();

        $data = ['thank_forum_id' => $forum_id,
            'thank_topic_id' => $topic_id,
            'thank_user_id' => $user_id,
            'thank_time' => time()];

        $this->thanksManager->add(\Nette\Utils\ArrayHash::from($data));
        $this->userManager->update($user_id, \Nette\Utils\ArrayHash::from(['user_thank_count%sql' => 'user_thank_count + 1']));

        $this->flashMessage('Your thank to this topic!', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Post:all', $forum_id, $topic_id);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int|null $page_id
     * 
     */
    public function renderAll($forum_id, $topic_id, $page_id = null, $page = 1) {
        $data = $this->getManager()->getPostsByTopicId($topic_id);

        $pagination = new \App\Controls\PaginatorControl($data, 10, 5, $page);
        $this->addComponent($pagination, 'paginator');

        if (!$pagination->getCount()) {
            $this->flashMessage('No posts.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->posts = $data->fetchAll();
        $this->template->topic = $this->topicsManager->getById($topic_id);
        $this->template->canThank = $this->thanksManager->canUserThank($forum_id, $topic_id, $this->getUser()->getId());
        $this->template->thanks = $this->thanksManager->getThanksWithUserInTopic($topic_id);
    }

    /**
     * 
     * @param int $forum_id
     * @param int $topic_id
     */
    public function renderEditTopic($forum_id, $topic_id = null) {
        $topic = [];

        if ($topic_id) {
            $topic = $this->topicsManager->getById($topic_id);
        }

        $this['editTopicForm']->setDefaults($topic);
    }

    /**
     * 
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public function renderEditPost($forum_id, $topic_id, $post_id = null) {
        $post = [];

        if ($post_id) {
            $post = $this->getManager()->getById($post_id);
        }

        $this['editPostForm']->setDefaults($post);
    }

    private function postForm() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->getForumTranslator());

        $form->addText('post_title', 'Title')->setRequired(true);
        $form->addTextArea('post_text', 'Text', 0, 15)->setRequired(true);
        $form->addSubmit('send', 'Send');

        return $form;
    }

    protected function createComponentEditTopicForm() {
        $form = $this->postForm();

        $form->onSuccess[] = [$this, 'editTopicFormSuccess'];

        return $form;
    }

    protected function createComponentEditPostForm() {
        $form = $this->postForm();

        $form->onSuccess[] = [$this, 'editPostFormSuccess'];

        return $form;
    }

    public function editTopicFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $forum_id = $this->getParameter('forum_id');
        $topic_id = $this->getParameter('topic_id');
        $user_id = $this->getUser()->getId();

        $post_topic_id = $this->topicsManager->add(\Nette\Utils\ArrayHash::from(['topic_forum_id' => $forum_id, 'topic_name' => $values->post_title, 'topic_post_count' => 1, 'topic_user_id' => $user_id, 'topic_last_post_user_id' => $user_id, 'topic_add_time' => time()]));

        $values->post_add_time = time();
        $values->post_user_id = $user_id;
        $values->post_forum_id = $forum_id;
        $values->post_topic_id = $post_topic_id;

        $post_id = $this->getManager()->add($values);

        $this->topicsManager->update($post_topic_id, \Nette\Utils\ArrayHash::from(['topic_last_post_id' => $post_id]));
        $this->userManager->update($user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count + 1', 'user_post_count%sql' => 'user_post_count + 1']));
        $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_post_id' => $post_id, 'forum_last_post_user_id' => $user_id, 'forum_last_topic_id' => $post_topic_id, 'forum_topic_count%sql' => 'forum_topic_count + 1']));

        $this->flashMessage('Topic saved.', self::FLASH_MESSAGE_SUCCES);
        $this->redirect('Post:all', $forum_id, $post_topic_id);
    }

    public function editPostFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $forum_id = $this->getParameter('forum_id');
        $post_id = $this->getParameter('post_id');
        $topic_id = $this->getParameter('topic_id');
        $user_id = $this->getUser()->getId();

        if ($post_id) {
            $values['post_edit_count%sql'] = 'post_edit_count + 1';
            $values->post_last_edit_time = time();

            $result = $this->getManager()->update($post_id, $values);
        } else {
            $values->post_forum_id = $forum_id;
            $values->post_user_id = $user_id;
            $values->post_topic_id = $topic_id;
            $values->post_add_time = time();

            $result = $post_id = $this->getManager()->add($values);

            $this->userManager->update($user_id, \Nette\Utils\ArrayHash::from(['user_topic_count%sql' => 'user_topic_count + 1']));
            $this->topicsManager->update($topic_id, \Nette\Utils\ArrayHash::from(['topic_post_count%sql' => 'topic_post_count+1', 'topic_last_post_user_id' => $user_id, 'topic_last_post_id' => $post_id]));
            $this->forumManager->update($forum_id, \Nette\Utils\ArrayHash::from(['forum_last_topic_id' => $topic_id, 'forum_last_post_id' => $post_id, 'forum_last_post_user_id' => $user_id]));
        }

        if ($result) {
            $this->flashMessage('Post saved.', self::FLASH_MESSAGE_SUCCES);
        } else {
            $this->flashMessage('Nothing to change.', self::FLASH_MESSAGE_INFO);
        }

        $this->redirect('Post:all', $forum_id, $topic_id);
    }

}
