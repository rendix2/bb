<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\ForumsManager;
use App\Models\ReportsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 * @method ReportsManager getManager()
 */
class ReportPresenter extends Base\AdminPresenter
{
    /**
     * @var UsersManager $userManager
     */
    private $userManager;
    /**
     * @var ForumsManager $forumManager
     */
    private $forumManager;
    /**
     * @var TopicsManager $topicManager
     */
    private $topicManager;

    /**
     * ReportPresenter constructor.
     *
     * @param ReportsManager $manager
     */
    public function __construct(ReportsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param ForumsManager $forumManager
     */
    public function injectForumManager(ForumsManager $forumManager)
    {
        $this->forumManager = $forumManager;
    }

    /**
     * @param TopicsManager $topicManager
     */
    public function injectTopicManager(TopicsManager $topicManager)
    {
        $this->topicManager = $topicManager;
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param int $forum_id forum_id
     */
    public function renderForum($forum_id)
    {
        if (!is_numeric($forum_id)) {
            $this->error('Parameter is not numeric.');
        }

        $forum = $this->forumManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forums does not exists.');
        }

        $items                 = $this->getManager()
            ->getByForumId($forum_id);
        $this->template->items = $items->fetchAll();
        $this->template->forum = $forum;
    }

    /**
     * @param int $topic_id topic_id
     */
    public function renderTopic($topic_id)
    {
        if (!is_numeric($topic_id)) {
            $this->error('Parameter is not numeric.');
        }

        $topic = $this->topicManager->getById($topic_id);

        if (!$topic) {
            $this->error('Topic does not exist.');
        }

        $items = $this->getManager()
            ->getByTopicId($topic_id);

        $this->template->items = $items->fetchAll();
        $this->template->topic = $topic;
    }

    /**
     * @param int $user_id user_id
     */
    public function renderUser($user_id)
    {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }

        $user = $this->userManager->getById($user_id);

        if (!$user) {
            $this->error('User does not exist.');
        }

        $items = $this->getManager()
            ->getByUserId($user_id);

        $this->template->items    = $items->fetchAll();
        $this->template->userData = $user;
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addSelect(
            'report_status',
            'Report status:',
            [
                0 => 'Added',
                1 => 'Fixed'
            ]
        );

        return $this->addSubmitB($form);
    }
}
