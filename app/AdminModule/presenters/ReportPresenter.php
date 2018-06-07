<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootStrapForm;
use App\Models\ForumsManager;
use App\Models\ReportsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use App\Controls\GridFilter;

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
    
    public function startup(){
        parent::startup();
        
        if ($this->getAction() == 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('report_id', 'report_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('report_time','report_time',GridFilter::TEXT_LIKE);
            $this->gf->addFilter('user_name', 'report_user', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('forum_name', 'report_forum', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('topic_name', 'report_topic', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('post_title', 'report_post', GridFilter::TEXT_LIKE);         
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
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
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $values = [
                0 => 'Added',
                1 => 'Fixed'
            ];
        
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addSelect('report_status', 'Report status:', $values);

        return $this->addSubmitB($form);
    }
}
