<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\ReportsManager;

/**
 * Description of ReportPresenter
 *
 * @author rendi
 * @method ReportsManager getManager()
 */
class ReportPresenter extends Base\AdminPresenter {
    
    private $userManager;
    
    private $forumManager;
    
    private $topicManager;
    
    

    /**
     * ReportPresenter constructor.
     *
     * @param ReportsManager $manager
     */
    public function __construct(ReportsManager $manager) {
        parent::__construct($manager);
    }
    
    
    public function injectUserManager(\App\Models\UsersManager $userManager){
        $this->userManager = $userManager;
    }
    
    public function injectTopicManager(\App\Models\TopicsManager $topicManager){
        $this->topicManager = $topicManager;
    }
    
    public function injectForumManager(\App\Models\ForumsManager $forumManager){
        $this->forumManager = $forumManager;
    }

    /**
     *
     */
    public function renderForum($forum_id) {
        if (!is_numeric($forum_id)) {
            $this->error('Parameter is not numeric.');
        }
        
        $forum = $this->forumManager->getById($forum_id);
        
        if ( !$forum ){
            $this->error('Forums does not exists.');
        }           

        $items = $this->getManager()->getByForumId($forum_id);
        $this->template->items = $items->fetchAll();
        $this->template->forum = $forum;
    }

    /**
     *
     */
    public function renderTopic($topic_id) {
        if (!is_numeric($topic_id)) {
            $this->error('Parameter is not numeric.');
        }
        
        $topic = $this->topicManager->getById($topic_id);
        
        if ( !$topic ){
            $this->error('Topic doesnt exists');
        }

        $items = $this->getManager()->getByTopicId($topic_id);
        $this->template->items = $items->fetchAll();
        $this->template->topic = $topic;
    }

    /**
     *
     */
    public function renderUser($user_id) {
        if (!is_numeric($user_id)) {
            $this->error('Parameter is not numeric.');
        }
        
        $user = $this->userManager->getById($user_id);
        
        if ( !$user ){
            $this->error('User does not exists');
        }

        $items = $this->getManager()->getByUserId($user_id);
        $this->template->items = $items->fetchAll();
        $this->template->userData  = $user;
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addSelect('report_status', 'Report status:', [0 => 'Added', 1 => 'Fixed']);

        return $this->addSubmitB($form);
    }

}
